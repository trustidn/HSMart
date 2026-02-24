<?php

namespace App\Domains\Tenant\Livewire\Admin;

use App\Domains\Tenant\Models\Tenant;
use App\Domains\Tenant\Services\TenantService;
use Livewire\Component;
use Livewire\WithPagination;

class TenantList extends Component
{
    use WithPagination;

    public ?int $deleteConfirmTenantId = null;

    public function getTenantsProperty()
    {
        return Tenant::query()
            ->withCount('users')
            ->with(['subscriptions' => fn ($q) => $q->orderByDesc('ends_at')->limit(1)])
            ->orderBy('name')
            ->paginate(10);
    }

    public function confirmDelete(int $tenantId): void
    {
        $this->deleteConfirmTenantId = $tenantId;
    }

    public function cancelDelete(): void
    {
        $this->deleteConfirmTenantId = null;
    }

    public function deleteTenant(): void
    {
        if ($this->deleteConfirmTenantId === null) {
            return;
        }
        $tenant = Tenant::find($this->deleteConfirmTenantId);
        if ($tenant) {
            app(TenantService::class)->delete($tenant);
            session()->flash('message', 'Tenant berhasil dihapus.');
        }
        $this->deleteConfirmTenantId = null;
        $this->redirectRoute('admin.tenants', navigate: true);
    }

    public function render()
    {
        return view('domains.tenant.livewire.admin.tenant-list')
            ->layout('layouts.app', ['title' => 'Kelola Tenant']);
    }
}
