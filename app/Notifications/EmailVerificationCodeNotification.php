<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EmailVerificationCodeNotification extends Notification
{
    use Queueable;

    private string $code;

    public function __construct(string $code)
    {
        $this->code = $code;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Ověření e-mailu')
            ->greeting('Ahoj!')
            ->line('K dokončení registrace zadejte do aplikace následující ověřovací kód:')
            ->line('Kód: ' . $this->code)
            ->line('Kód je platný po omezenou dobu. Pokud jste registraci nepožadovali, tento e-mail jednoduše ignorujte.');
    }
}
