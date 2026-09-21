<?php

// Define el espacio de nombres donde se encuentra esta notificación.
namespace App\Notifications;

// Importa el modelo VerificationCode (aunque en este archivo no se utiliza).
use App\Models\VerificationCode;

// Importa la clase base para crear notificaciones en Laravel.
use Illuminate\Notifications\Notification;

// Importa la clase que permite construir correos electrónicos.
use Illuminate\Notifications\Messages\MailMessage;

/**
 * Notificación encargada de enviar el código de activación
 * al correo electrónico del usuario después del registro.
 */
class AccountActivationNotification extends Notification
{
    /**
     * Constructor de la clase.
     *
     * Recibe el código de activación generado por el sistema
     * y lo almacena para incluirlo en el correo.
     *
     * @param string $code Código de activación de 6 dígitos.
     */
    public function __construct(public string $code) {}

    /**
     * Define por qué canal será enviada la notificación.
     *
     * En este caso solo se utiliza el correo electrónico.
     *
     * @param object $notifiable Usuario que recibirá la notificación.
     * @return array
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Construye el contenido del correo electrónico.
     *
     * Incluye:
     * - Asunto del correo.
     * - Saludo personalizado.
     * - Código de activación.
     * - Tiempo de expiración.
     * - Mensaje de seguridad.
     *
     * @param object $notifiable Usuario que recibirá el correo.
     * @return MailMessage
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)

            // Asunto del correo.
            ->subject('Activa tu cuenta - Mi Chuzito')

            // Saludo personalizado utilizando el nombre del usuario.
            ->greeting('Hola, ' . $notifiable->name)

            // Mensaje de bienvenida.
            ->line('Gracias por registrarte en Mi Chuzito.')

            // Informa que se enviará el código.
            ->line('Tu código de activación es:')

            // Muestra el código generado.
            ->line('**' . $this->code . '**')

            // Informa el tiempo de validez del código.
            ->line('Este código expira en 10 minutos.')

            // Mensaje de seguridad.
            ->line('Si no creaste esta cuenta, ignora este mensaje.')

            // Firma del correo.
            ->salutation('El equipo de Mi Chuzito');
    }
}