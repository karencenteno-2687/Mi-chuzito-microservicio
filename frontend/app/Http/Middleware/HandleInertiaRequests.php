<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware as InertiaMiddleware;

class HandleInertiaRequests extends InertiaMiddleware
{
    protected $rootView = 'app';

    public function version(Request $request): string|null
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $request->user() ? array_merge(
                    $request->user()->toArray(),
                    ['roles' => $request->user()->getRoleNames()]
                ) : null,
            ],
        ]);
    }
}