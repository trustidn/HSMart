<?php

namespace App\Domains\Tenant\Livewire\Admin;

use App\Domains\Tenant\Models\Tenant;
use App\Domains\Tenant\Services\TenantService;
use Livewire\Component;

class TenantForm extends Component
{
    public ?int $tenantId = null;

    public string $name = '';

    public string $slug = '';

    public function mount(?int $tenantId = null): void
    {
        if ($tenantId !== null) {
            $tenant = Tenant::find($tenantId);
            if (! $tenant) {
                abort(404);
            }
            $this->tenantId = $tenant->id;
            $this->name = $tenant->name;
            $this->slug = $tenant->slug;
        }
    }

    public function save(): void
    {
        if ($this->tenantId !== null) {
            $this->validate([
                'name' => ['required', 'string', 'max:255'],
                'slug' => ['required', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],
            ]);
            $tenant = Tenant::findOrFail($this->tenantId);
            app(TenantService::class)->update($tenant, [
                'name' => $this->name,
                'slug' => $this->slug,
            ]);
            session()->flash('message', 'Tenant berhasil diperbarui.');
        } else {
            $this->validate([
                'name' => ['required', 'string', 'max:255'],
            ]);
            app(TenantService::class)->create($this->name);
            session()->flash('message', 'Tenant berhasil dibuat.');
        }
        $this->redirectRoute('admin.tenants', navigate: true);
    }

    public function render()
    {
        $title = $this->tenantId ? 'Ubah Tenant' : 'Tenant Baru';

        return view('domains.tenant.livewire.admin.tenant-form')
            ->layout('layouts.app', ['title' => $title]);
    }
}
