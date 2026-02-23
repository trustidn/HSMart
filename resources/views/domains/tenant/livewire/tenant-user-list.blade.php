<div>
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <flux:heading size="xl">{{ __('Users') }}</flux:heading>
                <flux:subheading>{{ __('Manage users for this store. Only members can be added or edited here.') }}</flux:subheading>
            </div>
            <flux:button variant="primary" icon="plus" :href="route('users.create')" wire:navigate>
                {{ __('New User') }}
            </flux:button>
        </div>

        @if (session('message'))
            <flux:callout variant="success">{{ session('message') }}</flux:callout>
        @endif

        <flux:table>
            <flux:table.columns>
                <flux:table.row>
                    <flux:table.cell variant="strong">{{ __('Name') }}</flux:table.cell>
                    <flux:table.cell variant="strong">{{ __('Email') }}</flux:table.cell>
                    <flux:table.cell variant="strong">{{ __('Role') }}</flux:table.cell>
                    <flux:table.cell variant="strong" class="w-0"></flux:table.cell>
                </flux:table.row>
            </flux:table.columns>
            <flux:table.rows>
                @forelse ($this->users as $user)
                    <flux:table.row :key="$user->id">
                        <flux:table.cell>{{ $user->name }}</flux:table.cell>
                        <flux:table.cell>{{ $user->email }}</flux:table.cell>
                        <flux:table.cell>
                            @if($user->is_tenant_owner)
                                <flux:badge color="green">{{ __('Owner') }}</flux:badge>
                            @else
                                <flux:badge color="blue">{{ __('Member') }}</flux:badge>
                            @endif
                        </flux:table.cell>
                        <flux:table.cell>
                            @if(!$user->is_tenant_owner)
                                <flux:dropdown>
                                    <flux:button size="sm" variant="ghost" icon="ellipsis-horizontal" />
                                    <flux:menu>
                                        <flux:menu.item icon="pencil" :href="route('users.edit', ['userId' => $user->id])" wire:navigate>
                                            {{ __('Edit') }}
                                        </flux:menu.item>
                                        <flux:menu.item icon="trash" wire:click="confirmDelete({{ $user->id }})" class="text-red-600 dark:text-red-400">
                                            {{ __('Delete') }}
                                        </flux:menu.item>
                                    </flux:menu>
                                </flux:dropdown>
                            @else
                                —
                            @endif
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="4" class="text-center text-zinc-500 dark:text-zinc-400">
                            {{ __('No users yet.') }}
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>

        @if ($this->users->hasPages())
            <div class="mt-4">
                {{ $this->users->links() }}
            </div>
        @endif

        <flux:modal name="confirm-delete-tenant-user" :show="(bool) $deleteConfirmUserId" class="space-y-4">
            <flux:heading size="lg">{{ __('Delete user?') }}</flux:heading>
            <flux:text>{{ __('This user will be removed from this store.') }}</flux:text>
            <div class="flex justify-end gap-2">
                <flux:button variant="ghost" wire:click="cancelDelete">{{ __('Cancel') }}</flux:button>
                <flux:button variant="danger" wire:click="deleteUser">{{ __('Delete') }}</flux:button>
            </div>
        </flux:modal>
    </div>
</div>
