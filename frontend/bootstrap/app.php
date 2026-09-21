<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

/**
 * Configuración principal de la aplicación Laravel.
 *
 * En este archivo se inicializa la aplicación,
 * se registran las rutas, los middleware y el
 * manejo de excepciones antes de que el sistema
 * comience a atender las peticiones.
 */
return Application::configure(basePath: dirname(__DIR__))

    /**
     * Configuración de las rutas de la aplicación.
     *
     * - web.php: Rutas accesibles desde el navegador.
     * - api.php: Endpoints de la API.
     * - console.php: Comandos Artisan.
     * - health: Ruta utilizada para verificar
     *   el estado de la aplicación.
     */
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )

    /**
     * Registro de los middleware de la aplicación.
     *
     * Los middleware permiten ejecutar lógica antes
     * o después de que una petición llegue al controlador.
     */
    ->withMiddleware(function (Middleware $middleware) {

        /**
         * Agrega el middleware de Inertia a todas
         * las rutas web para compartir información
         * entre Laravel y Vue.
         */
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
        ]);

        /**
         * Registra alias para los middleware
         * de control de acceso utilizando
         * el paquete Spatie Permission.
         */
        $middleware->alias([

            // Verifica que el usuario tenga un rol específico.
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,

            // Verifica que el usuario tenga un permiso específico.
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,

            // Permite acceder si posee un rol o un permiso.
            'role_or_permission' =>
                \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);
    })

    /**
     * Configuración para el manejo global
     * de excepciones de la aplicación.
     *
     * Actualmente no contiene personalizaciones,
     * por lo que Laravel utiliza su comportamiento
     * predeterminado.
     */
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })

    // Finaliza la configuración y crea la aplicación.
    ->create();