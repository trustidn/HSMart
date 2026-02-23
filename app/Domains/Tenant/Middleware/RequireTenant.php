<?php

namespace App\Domains\Tenant\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireTenant
{
    /**
     * Redirect superadmin (no tenant) to admin tenants page; allow tenant users through.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (tenant() !== null) {
            return $next($request);
        }

        if ($request->routeIs('admin.*') || $request->routeIs('dashboard')) {
            return $next($request);
        }

        return redirect()->route('dashboard');
    }
}
