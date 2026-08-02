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

        foreach ($subscriptions as $sub) {
            if (!$sub->endpoint || !$sub->p256dh || !$sub->auth) {
                continue;
            }

            try {
                $subscription = Subscription::create([
                    'endpoint' => $sub->endpoint,
                    'keys' => [
                        'p256dh' => $sub->p256dh,
                        'auth' => $sub->auth,
                    ],
                    'contentEncoding' => $sub->content_encoding ?: 'aesgcm',
                ]);

                $webPush->queueNotification(
                    $subscription,
                    json_encode($data)
                );
            } catch (Throwable $e) {
                report($e);
            }
        }

        try {
            $sent = 0;
            foreach ($webPush->flush() as $report) {
                if ($report->isSuccess()) {
                    $sent++;
                    continue;
                }

                $endpoint = $report->getRequest()->getUri()->__toString();
                $code = $report->getResponse()?->getStatusCode();
                $reason = $report->getReason();

                Log::warning('Web push delivery failed', [
                    'endpoint' => $endpoint,
                    'status' => $code,
                    'reason' => $reason,
                ]);

                // Gone / Not Found => remove dead subscription
                if (in_array($code, [404, 410], true)) {
                    PushSubscription::where('endpoint', $endpoint)->delete();
                }
            }

            if ($sent > 0) {
                Log::info('Web push delivered', [
                    'user_id' => $notifiable->getKey(),
                    'sent' => $sent,
                    'notification' => $notification::class,
                ]);
            }
        } catch (Throwable $e) {
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
