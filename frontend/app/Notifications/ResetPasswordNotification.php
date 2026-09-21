<?php

// Espacio de nombres donde se encuentra la notificación.
namespace App\Notifications;

// Importa la clase base para crear notificaciones.
use Illuminate\Notifications\Notification;

// Importa la clase utilizada para construir correos electrónicos.
use Illuminate\Notifications\Messages\MailMessage;

/**
 * Notificación encargada de enviar un código
 * para recuperar la contraseña del usuario.
 */
class ResetPasswordNotification extends Notification
{
    /**
     * Constructor de la clase.
     *
     * Recibe el código de recuperación generado
     * por el sistema para enviarlo al usuario.
     *
     * @param string $code Código de recuperación.
     */
    public function __construct(public string $code) {}

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
     * Construye el contenido del correo electrónico.
     *
     * Incluye el código de recuperación,
     * el tiempo de expiración y un mensaje de seguridad.
     *
     * @param object $notifiable Usuario que recibirá el correo.
     * @return MailMessage
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)

            // Asunto del correo.
            ->subject('Recuperación de contraseña - Mi Chuzito')

            // Saludo personalizado con el nombre del usuario.
            ->greeting('Hola, ' . $notifiable->name)

            // Informa que se recibió una solicitud de recuperación.
            ->line('Recibimos una solicitud para restablecer tu contraseña.')

            // Indica que a continuación se muestra el código.
            ->line('Tu código de recuperación es:')

            // Muestra el código generado por el sistema.
            ->line('**' . $this->code . '**')

            // Informa el tiempo de validez del código.
            ->line('Este código expira en 15 minutos.')

            // Mensaje de seguridad en caso de que el usuario no haya solicitado el cambio.
            ->line('Si no solicitaste esto, ignora este mensaje.')

            // Firma del correo.
            ->salutation('El equipo de Mi Chuzito');
    }
}