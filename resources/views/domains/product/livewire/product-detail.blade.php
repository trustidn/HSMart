<div>
    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl">
        <div class="flex items-center gap-2">
            <flux:link :href="route('products.index')" wire:navigate icon="arrow-left" icon-position="left">
                {{ 'Produk' }}
            </flux:link>
        </div>

        @if ($this->product)
            <div class="grid gap-6 lg:grid-cols-3">
                <div class="space-y-4">
                    <flux:card>
                        @if ($this->product->imageUrl())
                            <img
                                src="{{ $this->product->imageUrl() }}"
                                alt="{{ $this->product->name }}"
                                class="aspect-square w-full rounded-lg object-cover"
                            />
                        @else
                            <div class="flex aspect-square w-full items-center justify-center rounded-lg bg-zinc-100 text-zinc-400 dark:bg-zinc-800">
                                <flux:icon name="photo" class="h-24 w-24" />
                            </div>
                        @endif
                        <flux:heading size="xl" class="mt-4">{{ $this->product->name }}</flux:heading>
                        <flux:subheading>{{ $this->product->sku }}{{ $this->product->barcode ? ' · ' . $this->product->barcode : '' }}</flux:subheading>
                        <div class="mt-4 flex gap-2">
                            <flux:button size="sm" variant="ghost" :href="route('products.edit', ['productId' => $this->product->id])" wire:navigate icon="pencil">
                                {{ 'Ubah' }}
                            </flux:button>
                        </div>
                    </flux:card>

                    <flux:card>
                        <flux:heading size="base">{{ 'Stok' }}</flux:heading>
                        <div class="mt-2 flex items-baseline gap-2">
                            <flux:heading size="2xl">{{ $this->product->stock }}</flux:heading>
                            <flux:text class="text-zinc-500">{{ 'unit' }}</flux:text>
                        </div>
                        @if ($this->product->isLowStock())
                            <flux:badge color="red" class="mt-2">{{ 'Stok rendah' }}</flux:badge>
                        @endif
                        <flux:button size="sm" variant="ghost" class="mt-3" wire:click="openAdjustStock({{ $this->product->id }})" icon="adjustments-horizontal">
                            {{ 'Sesuaikan Stok' }}
                        </flux:button>
                    </flux:card>

                    <flux:card>
                        <flux:heading size="base">{{ 'Penjualan' }}</flux:heading>
                        <div class="mt-2 space-y-2">
                            <div class="flex items-baseline justify-between gap-2">
                                <flux:text class="text-zinc-500">{{ 'Jumlah terjual' }}</flux:text>
                                <flux:heading size="lg">{{ number_format($this->salesStats['total_qty'], 0, ',', '.') }}</flux:heading>
                            </div>
                            <div class="flex items-baseline justify-between gap-2">
                                <flux:text class="text-zinc-500">{{ 'Total nilai penjualan' }}</flux:text>
                                <flux:heading size="lg">Rp {{ number_format($this->salesStats['total_value'], 0, ',', '.') }}</flux:heading>
                            </div>
                        </div>
                    </flux:card>
                </div>

                <div class="lg:col-span-2">
                    <flux:card>
                        <flux:heading size="base">{{ 'Pembelian (stok masuk)' }}</flux:heading>
                        <flux:table class="mt-4">
                            <flux:table.columns>
                                <flux:table.row>
                                    <flux:table.cell variant="strong">{{ 'Tanggal' }}</flux:table.cell>
                                    <flux:table.cell variant="strong">{{ 'Nomor pembelian' }}</flux:table.cell>
                                    <flux:table.cell variant="strong" align="end">{{ 'Jumlah' }}</flux:table.cell>
                                    <flux:table.cell variant="strong" align="end">{{ 'Harga beli' }}</flux:table.cell>
                                    <flux:table.cell variant="strong" align="end">{{ 'Subtotal' }}</flux:table.cell>
                                </flux:table.row>
                            </flux:table.columns>
                            <flux:table.rows>
                                @forelse ($this->purchaseItems as $item)
                                    <flux:table.row>
                                        <flux:table.cell>{{ $item->purchase?->purchase_date?->format('d/m/Y') ?? '—' }}</flux:table.cell>
                                        <flux:table.cell>{{ $item->purchase?->purchase_number ?? '—' }}</flux:table.cell>
                                        <flux:table.cell align="end">{{ $item->qty }}</flux:table.cell>
                                        <flux:table.cell align="end">{{ number_format($item->unit_cost, 0, ',', '.') }}</flux:table.cell>
                                        <flux:table.cell align="end">{{ number_format($item->subtotal, 0, ',', '.') }}</flux:table.cell>
                                    </flux:table.row>
                                @empty
                                    <flux:table.row>
                                        <flux:table.cell colspan="5" class="text-center text-zinc-500 dark:text-zinc-400">
                                            {{ 'Belum ada pembelian.' }}
                                        </flux:table.cell>
                                    </flux:table.row>
                                @endforelse
                            </flux:table.rows>
                        </flux:table>
                    </flux:card>
                </div>
            </div>
        @endif
    </div>

    @livewire(\App\Domains\Product\Livewire\StockAdjustment::class)
</div>
