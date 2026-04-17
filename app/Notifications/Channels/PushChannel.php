<?php

namespace App\Notifications\Channels;

use Illuminate\Notifications\Notification;
use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;

class PushChannel
{
    public function send($notifiable, Notification $notification)
{
    if (!method_exists($notification, 'toPush')) {
        return;
    }

    $data = $notification->toPush($notifiable);

    $subscriptions = $notifiable->pushSubscriptions;

    if (!$subscriptions || $subscriptions->isEmpty()) {
        return;
    }

    $webPush = new WebPush([
        "VAPID" => [
            "subject" => env('VAPID_SUBJECT'),
            "publicKey" => env('VAPID_PUBLIC_KEY'),
            "privateKey" => env('VAPID_PRIVATE_KEY'),
        ],
    ]);

    foreach ($subscriptions as $sub) {
        // Pro jistotu ověříme, že subskripce má potřebná data
        if (!$sub->endpoint || !$sub->p256dh || !$sub->auth) {
            continue;
        }

        $subscription = Subscription::create([
            "endpoint" => $sub->endpoint,
            "keys" => [
                "p256dh" => $sub->p256dh,
                "auth" => $sub->auth,
            ]
        ]);

        $webPush->queueNotification(
            $subscription,
            json_encode($data)
        );
    }

    $webPush->flush();
}
}