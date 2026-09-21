<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class TwoFactorCodeNotification extends Notification
{
    public function __construct(public string $code) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Código de verificación - Mi Chuzito')
            ->greeting('Hola, ' . $notifiable->name)
            ->line('Tu código de verificación es:')
            ->line('**' . $this->code . '**')
            ->line('Este código expira en 10 minutos.')
            ->line('Si no intentaste iniciar sesión, ignora este mensaje.')
            ->salutation('El equipo de Mi Chuzito');
    }
}