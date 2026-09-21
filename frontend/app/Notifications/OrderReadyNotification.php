<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * Notificación que avisa al repartidor que la comida
 * de un pedido ya está lista para ser recogida y entregada.
 */
class OrderReadyNotification extends Notification
{
    public function __construct(public Order $order) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Pedido #{$this->order->id} listo para entrega - Mi Chuzito")
            ->greeting("Hola, {$notifiable->name}")
            ->line("El pedido #{$this->order->id} ya está listo y esperando para ser recogido y entregado.")
            ->line("Dirección de entrega: {$this->order->address}")
            ->line("Total: $" . number_format($this->order->total, 0, ',', '.'))
            ->action('Ver pedido', url("/orders/{$this->order->id}"))
            ->salutation('El equipo de Mi Chuzito');
    }
}