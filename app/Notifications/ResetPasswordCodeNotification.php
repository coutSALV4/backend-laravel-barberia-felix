<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordCodeNotification extends Notification
{
    use Queueable;

    public function __construct(public string $code) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Tu código para restablecer tu contraseña')
            ->line('Usa el siguiente código para restablecer tu contraseña en la app:')
            ->line("**{$this->code}**")
            ->line('Este código expira en 10 minutos.')
            ->line('Si no solicitaste este cambio, ignora este mensaje.');
    }
}