<?php

namespace App\Domains\Tenant\Livewire\Admin;

use App\Domains\User\Services\UserService;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class UserList extends Component
{
    use WithPagination;

    public ?int $deleteConfirmUserId = null;

    public function getUsersProperty()
    {
        return User::query()
            ->with('tenant:id,name,slug')
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
            app(UserService::class)->delete($user, true);
            session()->flash('message', __('User deleted.'));
        }
        $this->deleteConfirmUserId = null;
        $this->redirectRoute('admin.users', navigate: true);
    }

    public function render()
    {
        return view('domains.tenant.livewire.admin.user-list')
            ->layout('layouts.app', ['title' => __('User Management')]);
    }
}
