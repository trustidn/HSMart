<?php

namespace App\Domains\Tenant\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantOwner
{
    /**
     * Allow only tenant owner to access (e.g. user management within tenant).
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (tenant() === null) {
            abort(403, __('Tenant context required.'));
        }

        if (! auth()->user()->isTenantOwner()) {
            abort(403, __('Only tenant owner can access this page.'));
        }

        return $next($request);
    }
}
