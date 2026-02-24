<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->replace(
            \Illuminate\Http\Middleware\TrustProxies::class,
            \App\Http\Middleware\TrustProxies::class
        );
        $middleware->alias([
            'tenant' => \App\Domains\Tenant\Middleware\ResolveTenantFromAuth::class,
            'require.tenant' => \App\Domains\Tenant\Middleware\RequireTenant::class,
            'superadmin' => \App\Domains\Tenant\Middleware\EnsureSuperadmin::class,
            'tenant.owner' => \App\Domains\Tenant\Middleware\EnsureTenantOwner::class,
            'tenant.edit.self.or.owner' => \App\Domains\Tenant\Middleware\AllowTenantUserEditSelfOrOwner::class,
            'subscription' => \App\Domains\Subscription\Middleware\CheckSubscription::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
