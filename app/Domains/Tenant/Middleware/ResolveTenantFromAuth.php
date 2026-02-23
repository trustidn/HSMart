<?php

namespace App\Domains\Tenant\Middleware;

use App\Domains\Tenant\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveTenantFromAuth
{
    /**
     * Set tenant context from authenticated user's tenant_id.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (! $user) {
            return $next($request);
        }

        if ($user->tenant_id === null) {
            // Superadmin: no tenant context
            return $next($request);
        }

        $tenant = Tenant::find($user->tenant_id);
        if (! $tenant) {
            abort(403, 'Tenant not found.');
        }

        app()->instance('tenant', $tenant);

        return $next($request);
    }
}
