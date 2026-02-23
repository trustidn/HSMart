<div>
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
            <flux:heading size="xl">{{ __('Tenant Management') }}</flux:heading>
            <flux:subheading>{{ __('Kelola semua tenant (Superadmin)') }}</flux:subheading>

            <flux:table>
                <flux:table.columns>
                    <flux:table.row>
                        <flux:table.cell variant="strong">{{ __('Name') }}</flux:table.cell>
                        <flux:table.cell variant="strong">{{ __('Slug') }}</flux:table.cell>
                        <flux:table.cell variant="strong" align="end">{{ __('Users') }}</flux:table.cell>
                        <flux:table.cell variant="strong">{{ __('Created') }}</flux:table.cell>
                    </flux:table.row>
                </flux:table.columns>
                <flux:table.rows>
                    @forelse ($this->tenants as $tenant)
                        <flux:table.row :key="$tenant->id">
                            <flux:table.cell>{{ $tenant->name }}</flux:table.cell>
                            <flux:table.cell>{{ $tenant->slug }}</flux:table.cell>
                            <flux:table.cell align="end">{{ $tenant->users_count }}</flux:table.cell>
                            <flux:table.cell>{{ $tenant->created_at->format('d/m/Y') }}</flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="4" class="text-center text-zinc-500 dark:text-zinc-400">
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
        </div>
</div>
