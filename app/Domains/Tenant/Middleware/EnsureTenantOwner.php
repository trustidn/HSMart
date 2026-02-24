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
            abort(403, 'Konteks tenant diperlukan.');
        }

        if (! auth()->user()->isTenantOwner()) {
            abort(403, 'Hanya pemilik tenant yang dapat mengakses halaman ini.');
        }

        return $next($request);
    }
}
