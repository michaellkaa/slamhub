<?php

namespace App\Notifications\Channels;

use App\Models\PushSubscription;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;
use Throwable;

class PushChannel
{
    public function send($notifiable, Notification $notification): void
    {
        if (!method_exists($notification, 'toPush')) {
            return;
        }

        $publicKey = config('services.vapid.public_key') ?: env('VAPID_PUBLIC_KEY');
        $privateKey = config('services.vapid.private_key') ?: env('VAPID_PRIVATE_KEY');
        $subject = $this->normalizeVapidSubject(
            config('services.vapid.subject') ?: env('VAPID_SUBJECT', config('app.url'))
        );

        if (!$publicKey || !$privateKey) {
            Log::warning('Push skipped: VAPID keys are not configured.');
            return;
        }

        $subscriptions = $notifiable->pushSubscriptions()->get();
        if ($subscriptions->isEmpty()) {
            Log::info('Push skipped: user has no push_subscriptions', [
                'user_id' => $notifiable->getKey(),
                'notification' => $notification::class,
            ]);
            return;
        }

        $data = $notification->toPush($notifiable);

        try {
            $webPush = new WebPush([
                'VAPID' => [
                    'subject' => $subject,
                    'publicKey' => $publicKey,
                    'privateKey' => $privateKey,
                ],
            ]);
        } catch (Throwable $e) {
            report($e);
            return;
        }

        $queued = 0;
        foreach ($subscriptions as $sub) {
            if (!$sub->endpoint || !$sub->p256dh || !$sub->auth) {
                Log::warning('Push skipped incomplete subscription row', [
                    'user_id' => $notifiable->getKey(),
                    'subscription_id' => $sub->id,
                    'has_endpoint' => (bool) $sub->endpoint,
                    'has_p256dh' => (bool) $sub->p256dh,
                    'has_auth' => (bool) $sub->auth,
                ]);
                continue;
            }

            try {
                $host = parse_url((string) $sub->endpoint, PHP_URL_HOST) ?: '';
                $encoding = $sub->content_encoding ?: 'aesgcm';

                // Apple Push Service expects aes128gcm; wrong encoding → silent/failed delivery.
                if (str_contains($host, 'web.push.apple.com')) {
                    $encoding = 'aes128gcm';
                }

                $subscription = Subscription::create([
                    'endpoint' => $sub->endpoint,
                    'keys' => [
                        'p256dh' => $sub->p256dh,
                        'auth' => $sub->auth,
                    ],
                    'contentEncoding' => $encoding,
                ]);

                $webPush->queueNotification(
                    $subscription,
                    json_encode($data)
                );
                $queued++;

                Log::info('Web push queued', [
                    'user_id' => $notifiable->getKey(),
                    'subscription_id' => $sub->id,
                    'encoding' => $encoding,
                    'endpoint_host' => $host,
                ]);
            } catch (Throwable $e) {
                Log::error('Web push queue failed', [
                    'user_id' => $notifiable->getKey(),
                    'subscription_id' => $sub->id,
                    'error' => $e->getMessage(),
                ]);
                report($e);
            }
        }

        if ($queued === 0) {
            Log::warning('Push skipped: nothing queued', [
                'user_id' => $notifiable->getKey(),
                'subscription_count' => $subscriptions->count(),
            ]);
            return;
        }

        try {
            $sent = 0;
            $failed = 0;
            foreach ($webPush->flush() as $report) {
                if ($report->isSuccess()) {
                    $sent++;
                    continue;
                }

                $failed++;
                $endpoint = $report->getRequest()->getUri()->__toString();
                $code = $report->getResponse()?->getStatusCode();
                $reason = $report->getReason();

                Log::warning('Web push delivery failed', [
                    'user_id' => $notifiable->getKey(),
                    'endpoint_host' => parse_url($endpoint, PHP_URL_HOST),
                    'status' => $code,
                    'reason' => $reason,
                ]);

                // Gone / Not Found => remove dead subscription
                if (in_array($code, [404, 410], true)) {
                    PushSubscription::where('endpoint', $endpoint)->delete();
                }
            }

            Log::info('Web push flush finished', [
                'user_id' => $notifiable->getKey(),
                'queued' => $queued,
                'sent' => $sent,
                'failed' => $failed,
                'notification' => $notification::class,
                'vapid_subject' => $subject,
            ]);
        } catch (Throwable $e) {
            Log::error('Web push flush exception', [
                'user_id' => $notifiable->getKey(),
                'error' => $e->getMessage(),
            ]);
            report($e);
        }
    }

    /**
     * VAPID subject must be mailto: or https: — plain http:// is rejected by push services.
     */
    private function normalizeVapidSubject(?string $subject): string
    {
        $subject = trim((string) $subject);

        if ($subject === '') {
            return 'mailto:admin@localhost';
        }

        if (str_starts_with($subject, 'mailto:')) {
            return $subject;
        }

        if (str_starts_with($subject, 'https://')) {
            return $subject;
        }

        // http://localhost or other http URLs → mailto fallback
        if (str_starts_with($subject, 'http://')) {
            return 'mailto:admin@localhost';
        }

        return 'mailto:'.$subject;
    }
}
