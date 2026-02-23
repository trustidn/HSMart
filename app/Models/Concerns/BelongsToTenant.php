<?php

namespace App\Models\Concerns;

use App\Domains\Tenant\Models\Tenant;
use Illuminate\Database\Eloquent\Builder;

trait BelongsToTenant
{
    /**
     * Boot the trait and apply global scope.
     */
    protected static function bootBelongsToTenant(): void
    {
        static::addGlobalScope('tenant', function (Builder $builder): void {
            $current = tenant();
            if ($current instanceof Tenant) {
                $builder->where($builder->getQuery()->from.'.tenant_id', $current->id);
            } else {
                $builder->whereRaw('1 = 0');
            }
        });
    }

    /**
     * Get the tenant that owns the model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<Tenant, $this>
     */
    public function tenant(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
