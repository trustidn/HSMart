<div>
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <flux:heading size="xl">{{ __('Tenant Management') }}</flux:heading>
                <flux:subheading>{{ __('Kelola semua tenant (Superadmin)') }}</flux:subheading>
            </div>
            <flux:button variant="primary" icon="plus" :href="route('admin.tenants.create')" wire:navigate>
                {{ __('New Tenant') }}
            </flux:button>
        </div>

        @if (session('message'))
            <flux:callout variant="success">{{ session('message') }}</flux:callout>
        @endif

        <flux:table>
            <flux:table.columns>
                <flux:table.row>
                    <flux:table.cell variant="strong">{{ __('Name') }}</flux:table.cell>
                    <flux:table.cell variant="strong">{{ __('Slug') }}</flux:table.cell>
                    <flux:table.cell variant="strong" align="end">{{ __('Users') }}</flux:table.cell>
                    <flux:table.cell variant="strong">{{ __('Subscription') }}</flux:table.cell>
                    <flux:table.cell variant="strong">{{ __('Created') }}</flux:table.cell>
                    <flux:table.cell variant="strong" class="w-0"></flux:table.cell>
                </flux:table.row>
            </flux:table.columns>
            <flux:table.rows>
                @forelse ($this->tenants as $tenant)
                    @php
                        $sub = $tenant->subscriptions->first();
                        $subActive = $sub && $sub->ends_at >= now();
                    @endphp
                    <flux:table.row :key="$tenant->id">
                        <flux:table.cell>{{ $tenant->name }}</flux:table.cell>
                        <flux:table.cell>{{ $tenant->slug }}</flux:table.cell>
                        <flux:table.cell align="end">{{ $tenant->users_count }}</flux:table.cell>
                        <flux:table.cell>
                            @if($sub)
                                <flux:badge :color="$subActive ? 'green' : 'red'">{{ $sub->ends_at->format('d/m/Y') }}</flux:badge>
                            @else
                                —
                            @endif
                        </flux:table.cell>
                        <flux:table.cell>{{ $tenant->created_at->format('d/m/Y') }}</flux:table.cell>
                        <flux:table.cell>
                            <flux:dropdown>
                                <flux:button size="sm" variant="ghost" icon="ellipsis-horizontal" />
                                <flux:menu>
                                    <flux:menu.item icon="pencil" :href="route('admin.tenants.edit', ['tenantId' => $tenant->id])" wire:navigate>
                                        {{ __('Edit') }}
                                    </flux:menu.item>
                                    <flux:menu.item icon="trash" wire:click="confirmDelete({{ $tenant->id }})" class="text-red-600 dark:text-red-400">
                                        {{ __('Delete') }}
                                    </flux:menu.item>
                                </flux:menu>
                            </flux:dropdown>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="6" class="text-center text-zinc-500 dark:text-zinc-400">
                            {{ __('No tenants yet.') }}
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>

        @if ($this->tenants->hasPages())
            <div class="mt-4">
                {{ $this->tenants->links() }}
            </div>
        @endif

        <flux:modal name="confirm-delete-tenant" :show="(bool) $deleteConfirmTenantId" class="space-y-4">
            <flux:heading size="lg">{{ __('Delete tenant?') }}</flux:heading>
            <flux:text>{{ __('This will delete the tenant and all related data (products, sales, etc.). Users will be unlinked. This cannot be undone.') }}</flux:text>
            <div class="flex justify-end gap-2">
                <flux:button variant="ghost" wire:click="cancelDelete">{{ __('Cancel') }}</flux:button>
                <flux:button variant="danger" wire:click="deleteTenant">{{ __('Delete') }}</flux:button>
            </div>
        </flux:modal>
    </div>
</div>
