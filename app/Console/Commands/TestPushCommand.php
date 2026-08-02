<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Notifications\Channels\PushChannel;
use Illuminate\Console\Command;
use Illuminate\Notifications\Notification;

class TestPushCommand extends Command
{
    protected $signature = 'push:test {user : Username or user ID}';

    protected $description = 'Send a test web-push notification to a user';

    public function handle(): int
    {
        $userArg = $this->argument('user');
        $user = is_numeric($userArg)
            ? User::find($userArg)
            : User::where('username', $userArg)->first();

        if (!$user) {
            $this->error('User not found.');
            return self::FAILURE;
        }

        $subs = $user->pushSubscriptions;
        $this->info("User #{$user->id} @{$user->username}");
        $this->info('push_subscriptions: '.$subs->count());
        $this->info('VAPID public: '.(config('services.vapid.public_key') ? 'yes' : 'NO'));
        $this->info('VAPID subject: '.(config('services.vapid.subject') ?: 'empty'));

        if ($subs->isEmpty()) {
            $this->error('No push_subscriptions for this user — enable notifications in the app first.');
            return self::FAILURE;
        }

        foreach ($subs as $sub) {
            $this->line('- #'.$sub->id.' enc='.($sub->content_encoding ?: '?').' '.substr((string) $sub->endpoint, 0, 72).'...');
        }

        $notification = new class extends Notification {
            public function via(object $notifiable): array
            {
                return ['push'];
            }

            public function toPush(object $notifiable): array
            {
                return [
                    'title' => 'SlamHub test',
                    'body' => 'Testovací push z artisan push:test',
                    'url' => '/',
                ];
            }
        };

        try {
            (new PushChannel())->send($user, $notification);
            $this->info('Push send attempted — check device + storage/logs/laravel.log');
            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error($e->getMessage());
            report($e);
            return self::FAILURE;
        }
    }
}
