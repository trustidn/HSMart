<div>
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
            <flux:heading size="xl">{{ __('Point of Sale') }}</flux:heading>

            <div class="grid gap-6 lg:grid-cols-3">
                <div class="lg:col-span-2 space-y-4">
                    <form wire:submit="addByBarcode" class="flex gap-2">
                        <flux:field class="relative flex-1">
                            <flux:input
                                wire:model.live.debounce.300ms="barcodeInput"
                                placeholder="{{ __('Scan or enter barcode / SKU') }}"
                                icon="magnifying-glass"
                                autocomplete="off"
                            />
                            @if (strlen(trim($barcodeInput)) >= 1)
                                <div
                                    class="absolute left-0 right-0 top-full z-20 mt-1 max-h-60 overflow-auto rounded-lg border border-zinc-200 bg-white shadow-lg dark:border-zinc-700 dark:bg-zinc-800"
                                >
                                    @forelse ($this->productSearchResults as $product)
                                        <button
                                            type="button"
                                            wire:click="selectProduct({{ $product->id }})"
                                            class="flex w-full flex-col gap-0.5 px-3 py-2 text-left text-sm transition hover:bg-zinc-100 dark:hover:bg-zinc-700"
                                        >
                                            <span class="font-medium text-zinc-900 dark:text-zinc-100">{{ $product->name }}</span>
                                            <span class="text-xs text-zinc-500 dark:text-zinc-400">{{ $product->sku }}{{ $product->barcode ? ' · ' . $product->barcode : '' }}</span>
                                        </button>
                                    @empty
                                        <div class="px-3 py-4 text-center text-sm text-zinc-500 dark:text-zinc-400">
                                            {{ __('No products found. Type name or SKU.') }}
                                        </div>
                                    @endforelse
                                </div>
                            @endif
                            <flux:error name="barcodeInput" />
                        </flux:field>
                        <flux:button type="submit" variant="primary">
                            {{ __('Add') }}
                        </flux:button>
                    </form>

                    <flux:table>
                        <flux:table.columns>
                            <flux:table.row>
                                <flux:table.cell variant="strong">{{ __('Product') }}</flux:table.cell>
                                <flux:table.cell variant="strong" align="end">{{ __('Qty') }}</flux:table.cell>
                                <flux:table.cell variant="strong" align="end">{{ __('Price') }}</flux:table.cell>
                                <flux:table.cell variant="strong" align="end">{{ __('Subtotal') }}</flux:table.cell>
                                <flux:table.cell variant="strong" class="w-0"></flux:table.cell>
                            </flux:table.row>
                        </flux:table.columns>
                        <flux:table.rows>
                            @forelse ($cart as $index => $row)
                                <flux:table.row :key="$index">
                                    <flux:table.cell>
                                        <span class="font-medium">{{ $row['name'] }}</span>
                                        <flux:text class="text-zinc-500">{{ $row['sku'] }}</flux:text>
                                    </flux:table.cell>
                                    <flux:table.cell align="end">
                                        <flux:input
                                            type="number"
                                            min="1"
                                            wire:model.live="cart.{{ $index }}.qty"
                                            class="w-20 text-right"
                                        />
                                    </flux:table.cell>
                                    <flux:table.cell align="end">{{ number_format($row['unit_price'], 0, ',', '.') }}</flux:table.cell>
                                    <flux:table.cell align="end">{{ number_format($row['subtotal'], 0, ',', '.') }}</flux:table.cell>
                                    <flux:table.cell>
                                        <flux:button size="sm" variant="ghost" icon="x-mark" wire:click="removeFromCart({{ $index }})" />
                                    </flux:table.cell>
                                </flux:table.row>
                            @empty
                                <flux:table.row>
                                    <flux:table.cell colspan="5" class="text-center text-zinc-500 dark:text-zinc-400">
                                        {{ __('Cart is empty. Scan or add products.') }}
                                    </flux:table.cell>
                                </flux:table.row>
                            @endforelse
                        </flux:table.rows>
                    </flux:table>
                </div>

                <div class="space-y-4">
                    <flux:field>
                        <flux:label>{{ __('Customer name') }}</flux:label>
                        <flux:input wire:model="customerName" />
                    </flux:field>
                    <flux:field>
                        <flux:label>{{ __('Payment method') }}</flux:label>
                        <flux:select wire:model="paymentMethod">
                            <option value="cash">{{ __('Cash') }}</option>
                            <option value="transfer">{{ __('Transfer') }}</option>
                            <option value="other">{{ __('Other') }}</option>
                        </flux:select>
                    </flux:field>
                    <div class="rounded-xl border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-900">
                        <flux:heading size="lg">{{ __('Total') }}</flux:heading>
                        <flux:heading size="2xl">{{ number_format($this->total, 0, ',', '.') }}</flux:heading>
                    </div>
                    <flux:error name="cart" />
                    <flux:error name="checkout" />
                    <flux:button
                        variant="primary"
                        class="w-full py-3 text-base"
                        wire:click="checkout"
                        wire:loading.attr="disabled"
                    >
                        <span wire:loading.remove>{{ __('Complete Sale') }}</span>
                        <span wire:loading>{{ __('Processing...') }}</span>
                    </flux:button>
                    @if (session()->has('sale-completed'))
                        <flux:callout variant="success">{{ __('Sale completed successfully.') }}</flux:callout>
                    @endif
                </div>
            </div>
        </div>
</div>
