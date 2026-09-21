<?php

// Espacio de nombres donde se encuentra la notificación.
namespace App\Notifications;

// Importa los modelos necesarios para obtener la información
// del pago y de la factura.
use App\Models\Invoice;
use App\Models\Payment;

// Importa la clase base para crear notificaciones.
use Illuminate\Notifications\Notification;

// Importa la clase utilizada para construir correos electrónicos.
use Illuminate\Notifications\Messages\MailMessage;

/**
 * Notificación que informa al cliente
 * cuando un pago ha sido confirmado.
 */
class PaymentConfirmedNotification extends Notification
{
    /**
     * Constructor de la notificación.
     *
     * Recibe el pago realizado y la factura generada
     * para incluir su información en el correo.
     *
     * @param Payment $payment Información del pago.
     * @param Invoice $invoice Información de la factura.
     */
    public function __construct(
        public Payment $payment,
        public Invoice $invoice
    ) {}

    /**
     * Define el canal por el cual será enviada la notificación.
     *
     * En este caso únicamente mediante correo electrónico.
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
     * Incluye los datos principales del pago,
     * la factura y un botón para consultarla.
     *
     * @param object $notifiable Usuario que recibirá el correo.
     * @return MailMessage
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)

            // Asunto del correo con el número de factura.
            ->subject('Pago confirmado - ' . $this->invoice->invoice_number)

            // Saludo personalizado.
            ->greeting('Hola, ' . $notifiable->name)

            // Mensaje de confirmación.
            ->line('Tu pago ha sido confirmado exitosamente.')

            // Muestra el método de pago utilizado.
            ->line('**Método:** ' . ucfirst($this->payment->method))

            // Muestra el valor pagado.
            ->line('**Monto:** $' . number_format($this->payment->amount, 2))

            // Muestra el número de la factura.
            ->line('**Factura:** ' . $this->invoice->invoice_number)

            // Muestra el total de la factura con IVA.
            ->line('**Total con IVA:** $' . number_format($this->invoice->total, 2))

            // Agrega un botón para consultar la factura.
            ->action('Ver factura', url('/payments/invoice/' . $this->invoice->id))

            // Mensaje de despedida.
            ->line('Gracias por tu compra.')

            // Firma del correo.
            ->salutation('El equipo de Mi Chuzito');
    }
}