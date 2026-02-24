<?php

namespace App\Domains\Tenant\Livewire;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Domains\Tenant\Models\Tenant;
use App\Domains\User\Services\UserService;
use App\Models\User;
use Livewire\Component;

class TenantUserForm extends Component
{
    use PasswordValidationRules;
    use ProfileValidationRules;

    public ?int $userId = null;

    public ?int $tenant_id = null;

    public string $name = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    public function mount(?int $userId = null): void
    {
        $this->tenant_id = tenant()?->id;

        if ($userId !== null) {
            $this->ensureTenant();
            $user = User::where('tenant_id', tenant()->id)->find($userId);
            if (! $user) {
                abort(404);
            }
            if ($user->isTenantOwner() && $user->id !== auth()->id()) {
                abort(403, 'Anda tidak dapat mengubah pemilik tenant di sini.');
            }
            $this->userId = $user->id;
            $this->name = $user->name;
            $this->email = $user->email;
        }
    }

    protected function ensureTenant(): void
    {
        if (function_exists('tenant') && tenant() !== null) {
            return;
        }
        if ($this->tenant_id !== null) {
            $tenant = Tenant::find($this->tenant_id);
            if ($tenant !== null) {
                app()->instance('tenant', $tenant);
            }
        }
    }

    public function save(): void
    {
        $this->ensureTenant();

        if ($this->userId !== null) {
            $this->validate([
                'name' => $this->nameRules(),
                'email' => $this->emailRules($this->userId),
                'password' => ['nullable', 'string', \Illuminate\Validation\Rules\Password::default(), 'confirmed'],
            ]);
            $user = User::where('tenant_id', tenant()->id)->findOrFail($this->userId);
            $data = [
                'name' => $this->name,
                'email' => $this->email,
            ];
            if ($this->password) {
                $data['password'] = $this->password;
            }
            app(UserService::class)->update($user, $data, false);
            session()->flash('message', 'Pengguna berhasil diperbarui.');
            if ($user->id === auth()->id() && ! auth()->user()->isTenantOwner()) {
                $this->redirectRoute('dashboard', navigate: true);

                return;
            }
        } else {
            if (tenant() === null) {
                abort(403, 'Konteks tenant diperlukan.');
            }
            $this->validate([
                'name' => $this->nameRules(),
                'email' => $this->emailRules(),
                'password' => $this->passwordRules(),
            ]);
            app(UserService::class)->create([
                'name' => $this->name,
                'email' => $this->email,
                'password' => $this->password,
            ], false);
            session()->flash('message', 'Pengguna berhasil dibuat.');
        }
        $this->redirectRoute('users.index', navigate: true);
    }

    public function isEditingSelf(): bool
    {
        return $this->userId === auth()->id();
    }

    public function render()
    {
        $title = $this->userId
            ? ($this->isEditingSelf() ? 'Ubah profil' : 'Ubah Pengguna')
            : 'Pengguna Baru';

        return view('domains.tenant.livewire.tenant-user-form', ['isEditingSelf' => $this->isEditingSelf()])
            ->layout('layouts.app', ['title' => $title]);
    }
}
