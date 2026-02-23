<?php

namespace App\Domains\Purchasing\Services;

use App\Domains\Purchasing\Models\Supplier;

class SupplierService
{
    /**
     * @param  array{name: string, contact?: string|null, phone?: string|null, address?: string|null}  $data
     */
    public function create(array $data): Supplier
    {
        $data['tenant_id'] = tenant()->id;

        return Supplier::create($data);
    }

    /**
     * @param  array{name?: string, contact?: string|null, phone?: string|null, address?: string|null}  $data
     */
    public function update(Supplier $supplier, array $data): Supplier
    {
        $supplier->update($data);

        return $supplier->fresh();
    }
}
