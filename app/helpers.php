<?php

use App\Domains\Tenant\Models\Tenant;

if (! function_exists('tenant')) {
    /**
     * Get the current tenant from context (set by ResolveTenantFromAuth middleware).
     */
    function tenant(): ?Tenant
    {
        return app()->has('tenant') ? app('tenant') : null;
    }
}
