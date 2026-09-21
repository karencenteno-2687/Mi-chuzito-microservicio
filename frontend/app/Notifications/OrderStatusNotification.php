<?php

// Espacio de nombres donde se encuentra la notificación.
namespace App\Notifications;

// Importa el modelo Order para acceder a la información del pedido.
use App\Models\Order;

// Importa la clase base para crear notificaciones en Laravel.
use Illuminate\Notifications\Notification;

// Importa la clase para construir correos electrónicos.
use Illuminate\Notifications\Messages\MailMessage;

/**
 * Notificación encargada de informar al cliente
 * cuando el estado de un pedido cambia.
 */
class OrderStatusNotification extends Notification
{
    /**
     * Constructor de la clase.
     *
     * Recibe el pedido cuyo estado fue actualizado
     * y lo almacena para utilizar su información
     * en el correo electrónico.
     *
     * @param Order $order Pedido actualizado.
     */
    public function __construct(public Order $order) {}

    /**
     * Define el canal por el cual será enviada la notificación.
     *
     * En este caso únicamente por correo electrónico.
     *
     * @param object $notifiable Usuario que recibirá la notificación.
     * @return array
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Construye el contenido del correo.
     *
     * Muestra el estado actualizado del pedido,
     * el total de la compra, la dirección de entrega
     * y un botón para consultar el pedido.
     *
     * @param object $notifiable Usuario que recibirá el correo.
     * @return MailMessage
     */
    public function toMail(object $notifiable): MailMessage
    {
        // Arreglo que convierte los estados internos
        // en nombres más amigables para el usuario.
        $statusLabels = [
            'en_proceso' => 'En proceso',
            'en_camino'  => 'En camino',
            'entregado'  => 'Entregado',
            'cancelado'  => 'Cancelado',
        ];

        // Obtiene la descripción del estado.
        // Si no existe en el arreglo, muestra el valor original.
        $label = $statusLabels[$this->order->status] ?? $this->order->status;

        // Construye el correo electrónico.
        return (new MailMessage)

            // Asunto del correo.
            ->subject("Tu pedido #{$this->order->id} está {$label} - Mi Chuzito")

            // Saludo personalizado.
            ->greeting("Hola, {$notifiable->name}")

            // Informa el nuevo estado del pedido.
            ->line("El estado de tu pedido #{$this->order->id} ha cambiado a: **{$label}**")

            // Muestra el valor total del pedido.
            ->line("Total: $" . number_format($this->order->total, 0, ',', '.'))

            // Muestra la dirección donde será entregado.
            ->line("Dirección de entrega: {$this->order->address}")

            // Agrega un botón para consultar el pedido.
            ->action('Ver mi pedido', url("/orders/{$this->order->id}"))

            // Firma del correo.
            ->salutation('El equipo de Mi Chuzito');
    }
}