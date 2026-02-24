<?php

namespace App\Domains\Tenant\Livewire\Admin;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Domains\Tenant\Models\Tenant;
use App\Domains\User\Services\UserService;
use App\Models\User;
use Livewire\Component;

class UserForm extends Component
{
    use PasswordValidationRules;
    use ProfileValidationRules;

    public ?int $userId = null;

    public string $name = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    public ?int $tenant_id = null;

    public bool $is_tenant_owner = false;

    public function mount(?int $userId = null): void
    {
        if ($userId !== null) {
            $user = User::with('tenant')->find($userId);
            if (! $user) {
                abort(404);
            }
            $this->userId = $user->id;
            $this->name = $user->name;
            $this->email = $user->email;
            $this->tenant_id = $user->tenant_id;
            $this->is_tenant_owner = $user->is_tenant_owner;
        }
    }

    public function getTenantsProperty()
    {
        return Tenant::orderBy('name')->get(['id', 'name']);
    }

    public function save(): void
    {
        if ($this->userId !== null) {
            $this->validate([
                'name' => $this->nameRules(),
                'email' => $this->emailRules($this->userId),
                'password' => ['nullable', 'string', \Illuminate\Validation\Rules\Password::default(), 'confirmed'],
                'tenant_id' => ['nullable', 'exists:tenants,id'],
                'is_tenant_owner' => ['boolean'],
            ]);
            $user = User::findOrFail($this->userId);
            $data = [
                'name' => $this->name,
                'email' => $this->email,
                'tenant_id' => $this->tenant_id,
                'is_tenant_owner' => $this->is_tenant_owner,
            ];
            if ($this->password) {
                $data['password'] = $this->password;
            }
            app(UserService::class)->update($user, $data, true);
            session()->flash('message', 'Pengguna berhasil diperbarui.');
        } else {
            $this->validate([
                'name' => $this->nameRules(),
                'email' => $this->emailRules(),
                'password' => $this->passwordRules(),
                'tenant_id' => ['nullable', 'exists:tenants,id'],
                'is_tenant_owner' => ['boolean'],
            ]);
            app(UserService::class)->create([
                'name' => $this->name,
                'email' => $this->email,
                'password' => $this->password,
                'tenant_id' => $this->tenant_id,
                'is_tenant_owner' => $this->is_tenant_owner,
            ], true);
            session()->flash('message', 'Pengguna berhasil dibuat.');
        }
        $this->redirectRoute('admin.users', navigate: true);
    }

    public function render()
    {
        return view('domains.tenant.livewire.admin.user-form')
            ->layout('layouts.app', ['title' => $this->userId ? 'Ubah Pengguna' : 'Pengguna Baru']);
    }
}
