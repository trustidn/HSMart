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
        if (! $user || $user->tenant_id === null) {
            abort(403, 'No tenant assigned to this user.');
        }

        $tenant = Tenant::find($user->tenant_id);
        if (! $tenant) {
            abort(403, 'Tenant not found.');
        }

        app()->instance('tenant', $tenant);

        return $next($request);
    }
}
