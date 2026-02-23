<?php

namespace App\Domains\Tenant\Livewire;

use App\Domains\User\Services\UserService;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class TenantUserList extends Component
{
    use WithPagination;

    public ?int $deleteConfirmUserId = null;

    public function getUsersProperty()
    {
        return User::query()
            ->where('tenant_id', tenant()->id)
            ->orderByRaw('is_tenant_owner DESC')
            ->orderBy('name')
            ->paginate(10);
    }

    public function confirmDelete(int $userId): void
    {
        $this->deleteConfirmUserId = $userId;
    }

    public function cancelDelete(): void
    {
        $this->deleteConfirmUserId = null;
    }

    public function deleteUser(): void
    {
        if ($this->deleteConfirmUserId === null) {
            return;
        }
        $user = User::find($this->deleteConfirmUserId);
        if ($user) {
            app(UserService::class)->delete($user, false);
            session()->flash('message', __('User deleted.'));
        }
        $this->deleteConfirmUserId = null;
        $this->redirectRoute('users.index', navigate: true);
    }

    public function render()
    {
        return view('domains.tenant.livewire.tenant-user-list')
            ->layout('layouts.app', ['title' => __('Users')]);
    }
}
