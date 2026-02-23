<?php

namespace App\Domains\Tenant\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSuperadmin
{
    /**
     * Allow only superadmin (no tenant) to access admin routes.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (tenant() !== null) {
            abort(403, 'Only superadmin can access this page.');
        }

        return $next($request);
    }
}
