<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * Clase encargada de enviar un correo de bienvenida
 * cuando un usuario completa exitosamente su registro.
 *
 * Esta notificación confirma la creación de la cuenta
 * e invita al usuario a iniciar sesión en la plataforma.
 */
class WelcomeNotification extends Notification
{
    /**
     * Define el canal por el que será enviada la notificación.
     *
     * @param object $notifiable Usuario que recibirá la notificación.
     * @return array Canales de envío.
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Construye el contenido del correo de bienvenida.
     *
     * El correo contiene:
     * - Un saludo personalizado.
     * - Un mensaje confirmando la creación de la cuenta.
     * - Un botón para iniciar sesión.
     * - Un mensaje de apoyo al usuario.
     *
     * @param object $notifiable Usuario que recibirá el correo.
     * @return MailMessage Correo listo para ser enviado.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)

            // Asunto del correo.
            ->subject('Bienvenido a Mi Chuzito')

            // Saludo personalizado.
            ->greeting('Hola, ' . $notifiable->name)

            // Confirma que la cuenta fue creada correctamente.
            ->line('Tu cuenta ha sido creada exitosamente.')

            // Invita al usuario a ingresar al sistema.
            ->line('Ya puedes iniciar sesión y comenzar a usar la plataforma.')

            // Botón que redirige a la página de inicio de sesión.
            ->action('Iniciar sesión', url('/login'))

            // Mensaje de apoyo al usuario.
            ->line('Si tienes alguna duda, no dudes en contactarnos.')

            // Firma del correo.
            ->salutation('El equipo de Mi Chuzito');
    }
}