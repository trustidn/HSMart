<?php

namespace App\Domains\Tenant\Livewire;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Domains\User\Services\UserService;
use App\Models\User;
use Livewire\Component;

class TenantUserForm extends Component
{
    use PasswordValidationRules;
    use ProfileValidationRules;

    public ?int $userId = null;

    public string $name = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    public function mount(?int $userId = null): void
    {
        if ($userId !== null) {
            $user = User::where('tenant_id', tenant()->id)->find($userId);
            if (! $user) {
                abort(404);
            }
            if ($user->isTenantOwner() && $user->id !== auth()->id()) {
                abort(403, __('You cannot edit the tenant owner here.'));
            }
            $this->userId = $user->id;
            $this->name = $user->name;
            $this->email = $user->email;
        }
    }

    public function save(): void
    {
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
            session()->flash('message', __('User updated.'));
            if ($user->id === auth()->id() && ! auth()->user()->isTenantOwner()) {
                $this->redirectRoute('dashboard', navigate: true);

                return;
            }
        } else {
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
            session()->flash('message', __('User created.'));
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
            ? ($this->isEditingSelf() ? __('Edit profile') : __('Edit User'))
            : __('New User');

        return view('domains.tenant.livewire.tenant-user-form', ['isEditingSelf' => $this->isEditingSelf()])
            ->layout('layouts.app', ['title' => $title]);
    }
}
