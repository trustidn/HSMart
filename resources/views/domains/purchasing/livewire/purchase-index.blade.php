<div>
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <flux:heading size="xl">{{ __('Purchases') }}</flux:heading>
                <flux:button variant="primary" icon="plus" :href="route('purchasing.purchases.create')" wire:navigate>
                    {{ __('New Purchase') }}
                </flux:button>
            </div>

            <flux:field>
                <flux:input wire:model.live.debounce.300ms="search" :placeholder="__('Search by PO number or supplier...')" icon="magnifying-glass" />
            </flux:field>

            <flux:table>
                <flux:table.columns>
                    <flux:table.row>
                        <flux:table.cell variant="strong">{{ __('PO Number') }}</flux:table.cell>
                        <flux:table.cell variant="strong">{{ __('Date') }}</flux:table.cell>
                        <flux:table.cell variant="strong">{{ __('Supplier') }}</flux:table.cell>
                        <flux:table.cell variant="strong">{{ __('Items') }}</flux:table.cell>
                        <flux:table.cell variant="strong" align="end">{{ __('Total') }}</flux:table.cell>
                        <flux:table.cell variant="strong">{{ __('Status') }}</flux:table.cell>
                    </flux:table.row>
                </flux:table.columns>
                <flux:table.rows>
                    @forelse ($this->purchases as $purchase)
                        <flux:table.row :key="$purchase->id">
                            <flux:table.cell>{{ $purchase->purchase_number }}</flux:table.cell>
                            <flux:table.cell>{{ $purchase->purchase_date->format('d/m/Y') }}</flux:table.cell>
                            <flux:table.cell>{{ $purchase->supplier?->name ?? '—' }}</flux:table.cell>
                            <flux:table.cell class="max-w-xs">
                                @php
                                    $itemLabels = $purchase->items->map(fn ($i) => ($i->product?->name ?? '—') . ' × ' . $i->qty);
                                @endphp
                                <span class="line-clamp-2" title="{{ $itemLabels->join(', ') }}">{{ $itemLabels->join(', ') }}</span>
                            </flux:table.cell>
                            <flux:table.cell align="end">{{ number_format($purchase->total_amount, 0, ',', '.') }}</flux:table.cell>
                            <flux:table.cell>
                                <flux:badge :color="$purchase->status === 'completed' ? 'green' : 'zinc'">
                                    {{ ucfirst($purchase->status) }}
                                </flux:badge>
                            </flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="6" class="text-center text-zinc-500 dark:text-zinc-400">
                                {{ __('No purchases found.') }}
                            </flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>

            @if ($this->purchases->hasPages())
                <div class="mt-4">
                    {{ $this->purchases->links() }}
                </div>
            @endif
        </div>
</div>
