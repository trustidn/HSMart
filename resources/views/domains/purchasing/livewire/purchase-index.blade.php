<div>
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <flux:heading size="xl">{{ 'Pembelian' }}</flux:heading>
                <flux:button variant="primary" icon="plus" :href="route('purchasing.purchases.create')" wire:navigate>
                    {{ 'Pembelian Baru' }}
                </flux:button>
            </div>

            <flux:field>
                <flux:input wire:model.live.debounce.300ms="search" :placeholder="'Cari nomor PO atau pemasok...'" icon="magnifying-glass" />
            </flux:field>

            <flux:table>
                <flux:table.columns>
                    <flux:table.row>
                        <flux:table.cell variant="strong">{{ 'Nomor PO' }}</flux:table.cell>
                        <flux:table.cell variant="strong">{{ 'Tanggal' }}</flux:table.cell>
                        <flux:table.cell variant="strong">{{ 'Pemasok' }}</flux:table.cell>
                        <flux:table.cell variant="strong">{{ 'Item' }}</flux:table.cell>
                        <flux:table.cell variant="strong" align="end">{{ 'Total' }}</flux:table.cell>
                        <flux:table.cell variant="strong">{{ 'Status' }}</flux:table.cell>
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
                                {{ 'Pembelian tidak ditemukan.' }}
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
