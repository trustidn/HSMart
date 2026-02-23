<div>
    <x-layouts::app title="{{ __('Suppliers') }}">
        <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <flux:heading size="xl">{{ __('Suppliers') }}</flux:heading>
                <flux:button variant="primary" icon="plus" :href="route('purchasing.suppliers.create')" wire:navigate>
                    {{ __('New Supplier') }}
                </flux:button>
            </div>

            <flux:field>
                <flux:input wire:model.live.debounce.300ms="search" :placeholder="__('Search by name, contact, phone...')" icon="magnifying-glass" />
            </flux:field>

            <flux:table>
                <flux:table.columns>
                    <flux:table.row>
                        <flux:table.cell variant="strong">{{ __('Name') }}</flux:table.cell>
                        <flux:table.cell variant="strong">{{ __('Contact') }}</flux:table.cell>
                        <flux:table.cell variant="strong">{{ __('Phone') }}</flux:table.cell>
                        <flux:table.cell variant="strong">{{ __('Address') }}</flux:table.cell>
                        <flux:table.cell variant="strong" class="w-0"></flux:table.cell>
                    </flux:table.row>
                </flux:table.columns>
                <flux:table.rows>
                    @forelse ($this->suppliers as $supplier)
                        <flux:table.row :key="$supplier->id">
                            <flux:table.cell>{{ $supplier->name }}</flux:table.cell>
                            <flux:table.cell>{{ $supplier->contact ?? '—' }}</flux:table.cell>
                            <flux:table.cell>{{ $supplier->phone ?? '—' }}</flux:table.cell>
                            <flux:table.cell>{{ \Illuminate\Support\Str::limit($supplier->address, 40) ?: '—' }}</flux:table.cell>
                            <flux:table.cell>
                                <flux:button size="sm" variant="ghost" icon="pencil" :href="route('purchasing.suppliers.edit', ['supplierId' => $supplier->id])" wire:navigate>
                                    {{ __('Edit') }}
                                </flux:button>
                            </flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="5" class="text-center text-zinc-500 dark:text-zinc-400">
                                {{ __('No suppliers found.') }}
                            </flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>

            @if ($this->suppliers->hasPages())
                <div class="mt-4">
                    {{ $this->suppliers->links() }}
                </div>
            @endif
        </div>
    </x-layouts::app>
</div>
