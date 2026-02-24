<?php

namespace App\Domains\Tenant\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AllowTenantUserEditSelfOrOwner
{
    /**
     * Allow tenant user to edit own profile (users/{userId}/edit when userId === auth()->id())
     * or tenant owner to edit any user in the tenant.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (tenant() === null) {
            abort(403, 'Konteks tenant diperlukan.');
        }

        $userId = (int) $request->route('userId');

        if ($userId === auth()->id()) {
            return $next($request);
        }

        if (auth()->user()->isTenantOwner()) {
            return $next($request);
        }

        abort(403, 'Hanya pemilik tenant yang dapat mengubah pengguna lain.');
    }
}
