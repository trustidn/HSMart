<?php

namespace App\Domains\Tenant\Livewire\Admin;

use App\Domains\Tenant\Models\Tenant;
use Livewire\Component;
use Livewire\WithPagination;

class TenantList extends Component
{
    use WithPagination;

    public function getTenantsProperty()
    {
        return Tenant::query()
            ->withCount('users')
            ->orderBy('name')
            ->paginate(10);
    }

    public function render()
    {
        return view('domains.tenant.livewire.admin.tenant-list');
    }
}
