<div>
    <x-layouts::app title="{{ __('Reports') }}">
        <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl">
            <flux:heading size="xl">{{ __('Reports') }}</flux:heading>

            <flux:card class="flex flex-wrap items-end gap-4">
                <flux:field>
                    <flux:label>{{ __('From') }}</flux:label>
                    <flux:input type="date" wire:model.live="date_from" />
                </flux:field>
                <flux:field>
                    <flux:label>{{ __('To') }}</flux:label>
                    <flux:input type="date" wire:model.live="date_to" />
                </flux:field>
            </flux:card>

            <div class="grid gap-6 sm:grid-cols-2">
                <flux:card>
                    <flux:heading size="lg">{{ __('Revenue summary') }}</flux:heading>
                    <flux:text class="mt-2 text-2xl font-semibold">
                        {{ number_format($this->ringkasanOmzet['total'], 0, ',', '.') }}
                    </flux:text>
                    <flux:text class="text-zinc-500 dark:text-zinc-400">
                        {{ $this->ringkasanOmzet['count'] }} {{ __('transactions') }}
                        ({{ \Carbon\Carbon::parse($this->ringkasanOmzet['from'])->format('d/m/Y') }} – {{ \Carbon\Carbon::parse($this->ringkasanOmzet['to'])->format('d/m/Y') }})
                    </flux:text>
                </flux:card>
                <flux:card>
                    <flux:heading size="lg">{{ __('Profit & loss') }}</flux:heading>
                    @php($lr = $this->labaRugi)
                    <div class="mt-2 space-y-1 text-sm">
                        <div class="flex justify-between">
                            <span class="text-zinc-500 dark:text-zinc-400">{{ __('Revenue') }}</span>
                            <span>{{ number_format($lr['revenue'], 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-zinc-500 dark:text-zinc-400">{{ __('COGS') }}</span>
                            <span>{{ number_format($lr['cogs'], 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between border-t border-zinc-200 pt-2 dark:border-zinc-700">
                            <span class="font-medium">{{ __('Gross profit') }}</span>
                            <span class="font-medium">{{ number_format($lr['gross_profit'], 0, ',', '.') }}</span>
                        </div>
                    </div>
                </flux:card>
            </div>

            <flux:card>
                <flux:heading size="lg" class="mb-4">{{ __('Sales report') }}</flux:heading>
                <flux:table>
                    <flux:table.columns>
                        <flux:table.row>
                            <flux:table.cell variant="strong">{{ __('Invoice') }}</flux:table.cell>
                            <flux:table.cell variant="strong">{{ __('Date') }}</flux:table.cell>
                            <flux:table.cell variant="strong">{{ __('Customer') }}</flux:table.cell>
                            <flux:table.cell variant="strong" align="end">{{ __('Total') }}</flux:table.cell>
                        </flux:table.row>
                    </flux:table.columns>
                    <flux:table.rows>
                        @forelse ($this->laporanPenjualan as $sale)
                            <flux:table.row :key="$sale->id">
                                <flux:table.cell>{{ $sale->sale_number }}</flux:table.cell>
                                <flux:table.cell>{{ $sale->sale_date->format('d/m/Y') }}</flux:table.cell>
                                <flux:table.cell>{{ $sale->customer_name ?? '—' }}</flux:table.cell>
                                <flux:table.cell align="end">{{ number_format($sale->total_amount, 0, ',', '.') }}</flux:table.cell>
                            </flux:table.row>
                        @empty
                            <flux:table.row>
                                <flux:table.cell colspan="4" class="text-center text-zinc-500 dark:text-zinc-400">
                                    {{ __('No sales in this period.') }}
                                </flux:table.cell>
                            </flux:table.row>
                        @endforelse
                    </flux:table.rows>
                </flux:table>
                @if ($this->laporanPenjualan->hasPages())
                    <div class="mt-4">
                        {{ $this->laporanPenjualan->links() }}
                    </div>
                @endif
            </flux:card>

            <flux:card>
                <flux:heading size="lg" class="mb-4">{{ __('Top products') }}</flux:heading>
                <flux:table>
                    <flux:table.columns>
                        <flux:table.row>
                            <flux:table.cell variant="strong">{{ __('Product') }}</flux:table.cell>
                            <flux:table.cell variant="strong">{{ __('SKU') }}</flux:table.cell>
                            <flux:table.cell variant="strong" align="end">{{ __('Qty sold') }}</flux:table.cell>
                            <flux:table.cell variant="strong" align="end">{{ __('Revenue') }}</flux:table.cell>
                        </flux:table.row>
                    </flux:table.columns>
                    <flux:table.rows>
                        @forelse ($this->topProduk as $row)
                            <flux:table.row :key="$row->product_id">
                                <flux:table.cell>{{ $row->product_name }}</flux:table.cell>
                                <flux:table.cell>{{ $row->product_sku }}</flux:table.cell>
                                <flux:table.cell align="end">{{ number_format($row->total_qty, 0, ',', '.') }}</flux:table.cell>
                                <flux:table.cell align="end">{{ number_format($row->total_revenue, 0, ',', '.') }}</flux:table.cell>
                            </flux:table.row>
                        @empty
                            <flux:table.row>
                                <flux:table.cell colspan="4" class="text-center text-zinc-500 dark:text-zinc-400">
                                    {{ __('No sales in this period.') }}
                                </flux:table.cell>
                            </flux:table.row>
                        @endforelse
                    </flux:table.rows>
                </flux:table>
            </flux:card>

            <flux:card>
                <div class="mb-4 flex items-center justify-between">
                    <flux:heading size="lg">{{ __('Stock report') }}</flux:heading>
                    <flux:field class="mb-0">
                        <flux:checkbox wire:model.live="low_stock_only" :label="__('Low stock only')" />
                    </flux:field>
                </div>
                <flux:table>
                    <flux:table.columns>
                        <flux:table.row>
                            <flux:table.cell variant="strong">{{ __('SKU') }}</flux:table.cell>
                            <flux:table.cell variant="strong">{{ __('Name') }}</flux:table.cell>
                            <flux:table.cell variant="strong" align="end">{{ __('Stock') }}</flux:table.cell>
                            <flux:table.cell variant="strong" align="end">{{ __('Min. stock') }}</flux:table.cell>
                            <flux:table.cell variant="strong">{{ __('Status') }}</flux:table.cell>
                        </flux:table.row>
                    </flux:table.columns>
                    <flux:table.rows>
                        @forelse ($this->laporanStok as $product)
                            <flux:table.row :key="$product->id">
                                <flux:table.cell>{{ $product->sku }}</flux:table.cell>
                                <flux:table.cell>{{ $product->name }}</flux:table.cell>
                                <flux:table.cell align="end">{{ $product->stock }}</flux:table.cell>
                                <flux:table.cell align="end">{{ $product->minimum_stock }}</flux:table.cell>
                                <flux:table.cell>
                                    @if($product->isLowStock())
                                        <flux:badge color="red">{{ __('Low stock') }}</flux:badge>
                                    @else
                                        <flux:badge color="green">{{ __('OK') }}</flux:badge>
                                    @endif
                                </flux:table.cell>
                            </flux:table.row>
                        @empty
                            <flux:table.row>
                                <flux:table.cell colspan="5" class="text-center text-zinc-500 dark:text-zinc-400">
                                    {{ $low_stock_only ? __('No products with low stock.') : __('No products.') }}
                                </flux:table.cell>
                            </flux:table.row>
                        @endforelse
                    </flux:table.rows>
                </flux:table>
            </flux:card>
        </div>
    </x-layouts::app>
</div>
