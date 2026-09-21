<?php

/**
 * Configuración principal de la aplicación.
 *
 * Este archivo define los parámetros generales del sistema,
 * como el nombre de la aplicación, el entorno de ejecución,
 * la zona horaria, el idioma, la clave de cifrado y el modo
 * de mantenimiento.
 */

return [

    /**
     * Nombre de la aplicación.
     *
     * Se utiliza en correos electrónicos, notificaciones
     * y otros elementos de la interfaz.
     */
    'name' => env('APP_NAME', 'Laravel'),

    /**
     * Entorno de ejecución.
     *
     * Define si la aplicación se encuentra en:
     * - local
     * - development
     * - testing
     * - production
     *
     * Se configura desde el archivo .env.
     */
    'env' => env('APP_ENV', 'production'),

    /**
     * Modo depuración.
     *
     * Cuando está en true Laravel muestra información
     * detallada de los errores para facilitar el desarrollo.
     *
     * En producción debe permanecer en false.
     */
    'debug' => (bool) env('APP_DEBUG', false),

    /**
     * URL principal de la aplicación.
     *
     * Es utilizada para generar enlaces correctamente
     * desde Artisan, correos electrónicos y notificaciones.
     */
    'url' => env('APP_URL', 'http://localhost'),

    /**
     * Zona horaria utilizada por la aplicación.
     *
     * Todas las fechas y horas del sistema utilizarán
     * esta configuración.
     */
    'timezone' => 'UTC',

    /**
     * Idioma principal de la aplicación.
     *
     * Laravel utilizará este idioma para mensajes,
     * validaciones y traducciones.
     */
    'locale' => env('APP_LOCALE', 'en'),

    /**
     * Idioma de respaldo.
     *
     * Se utiliza cuando no existe una traducción
     * en el idioma principal.
     */
    'fallback_locale' => env('APP_FALLBACK_LOCALE', 'en'),

    /**
     * Configuración utilizada por Faker para generar
     * datos de prueba durante el desarrollo.
     */
    'faker_locale' => env('APP_FAKER_LOCALE', 'en_US'),

    /**
     * Algoritmo de cifrado utilizado por Laravel.
     *
     * Se emplea para proteger información sensible
     * mediante encriptación.
     */
    'cipher' => 'AES-256-CBC',

    /**
     * Clave principal de cifrado.
     *
     * Laravel utiliza esta clave para cifrar cookies,
     * sesiones, tokens y otros datos sensibles.
     * Se obtiene desde el archivo .env.
     */
    'key' => env('APP_KEY'),

    /**
     * Claves anteriores de cifrado.
     *
     * Permiten mantener compatibilidad con datos
     * cifrados utilizando claves antiguas.
     */
    'previous_keys' => [
        ...array_filter(
            explode(',', env('APP_PREVIOUS_KEYS', ''))
        ),
    ],

    /**
     * Configuración del modo mantenimiento.
     *
     * Permite poner la aplicación fuera de servicio
     * temporalmente durante actualizaciones.
     */
    'maintenance' => [

        // Método utilizado para controlar el modo mantenimiento.
        'driver' => env('APP_MAINTENANCE_DRIVER', 'file'),

        // Almacén donde se guarda el estado del mantenimiento.
        'store' => env('APP_MAINTENANCE_STORE', 'database'),
    ],

];