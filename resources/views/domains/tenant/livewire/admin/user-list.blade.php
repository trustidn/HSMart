<div>
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <flux:heading size="xl">{{ 'Manajemen Pengguna' }}</flux:heading>
                <flux:subheading>{{ 'Buat dan kelola pengguna (Superadmin). Pemilik tenant hanya bisa dibuat di sini.' }}</flux:subheading>
            </div>
            <flux:button variant="primary" icon="plus" :href="route('admin.users.create')" wire:navigate>
                {{ 'Pengguna Baru' }}
            </flux:button>
        </div>

        @if (session('message'))
            <flux:callout variant="success">{{ session('message') }}</flux:callout>
        @endif

        <flux:table>
            <flux:table.columns>
                <flux:table.row>
                    <flux:table.cell variant="strong">{{ 'Nama' }}</flux:table.cell>
                    <flux:table.cell variant="strong">{{ 'Email' }}</flux:table.cell>
                    <flux:table.cell variant="strong">{{ 'Tenant' }}</flux:table.cell>
                    <flux:table.cell variant="strong">{{ 'Peran' }}</flux:table.cell>
                    <flux:table.cell variant="strong" class="w-0"></flux:table.cell>
                </flux:table.row>
            </flux:table.columns>
            <flux:table.rows>
                @forelse ($this->users as $user)
                    <flux:table.row :key="$user->id">
                        <flux:table.cell>{{ $user->name }}</flux:table.cell>
                        <flux:table.cell>{{ $user->email }}</flux:table.cell>
                        <flux:table.cell>{{ $user->tenant?->name ?? '—' }}</flux:table.cell>
                        <flux:table.cell>
                            @if($user->tenant_id === null)
                                <flux:badge color="zinc">{{ 'Superadmin' }}</flux:badge>
                            @elseif($user->is_tenant_owner)
                                <flux:badge color="green">{{ 'Pemilik' }}</flux:badge>
                            @else
                                <flux:badge color="blue">{{ 'Anggota' }}</flux:badge>
                            @endif
                        </flux:table.cell>
                        <flux:table.cell>
                            <flux:dropdown>
                                <flux:button size="sm" variant="ghost" icon="ellipsis-horizontal" />
                                <flux:menu>
                                    <flux:menu.item icon="pencil" :href="route('admin.users.edit', ['userId' => $user->id])" wire:navigate>
                                        {{ 'Ubah' }}
                                    </flux:menu.item>
                                    <flux:menu.item icon="trash" wire:click="confirmDelete({{ $user->id }})" class="text-red-600 dark:text-red-400">
                                        {{ 'Hapus' }}
                                    </flux:menu.item>
                                </flux:menu>
                            </flux:dropdown>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="5" class="text-center text-zinc-500 dark:text-zinc-400">
                            {{ 'Belum ada pengguna.' }}
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

        <flux:modal name="confirm-delete-user" :show="(bool) $deleteConfirmUserId" class="space-y-4">
            <flux:heading size="lg">{{ 'Hapus pengguna?' }}</flux:heading>
            <flux:text>{{ 'Pengguna ini akan dihapus. Tindakan ini tidak dapat dibatalkan.' }}</flux:text>
            <div class="flex justify-end gap-2">
                <flux:button variant="ghost" wire:click="cancelDelete">{{ 'Batal' }}</flux:button>
                <flux:button variant="danger" wire:click="deleteUser">{{ 'Hapus' }}</flux:button>
            </div>
        </flux:modal>
    </div>
</div>
