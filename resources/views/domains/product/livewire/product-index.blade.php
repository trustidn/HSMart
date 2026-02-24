<div>
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <flux:heading size="xl">{{ __('Products') }}</flux:heading>
            <flux:button variant="primary" icon="plus" :href="route('products.create')" wire:navigate>
                {{ __('New Product') }}
            </flux:button>
        </div>

        <flux:field>
            <flux:input wire:model.live.debounce.300ms="search" :placeholder="__('Search by name, SKU, barcode...')" icon="magnifying-glass" />
        </flux:field>

        <flux:table>
            <flux:table.columns>
                <flux:table.row>
                    <flux:table.cell variant="strong" class="w-16">{{ __('Photo') }}</flux:table.cell>
                    <flux:table.cell variant="strong">{{ __('SKU') }}</flux:table.cell>
                    <flux:table.cell variant="strong">{{ __('Name') }}</flux:table.cell>
                    <flux:table.cell variant="strong">{{ __('Barcode') }}</flux:table.cell>
                    <flux:table.cell variant="strong" align="end">{{ __('Stock') }}</flux:table.cell>
                    <flux:table.cell variant="strong" align="end">{{ __('Sell Price') }}</flux:table.cell>
                    <flux:table.cell variant="strong" align="end">{{ __('Status') }}</flux:table.cell>
                    <flux:table.cell variant="strong" class="w-0"></flux:table.cell>
                </flux:table.row>
            </flux:table.columns>
            <flux:table.rows>
                @forelse ($this->products as $product)
                    <flux:table.row :key="$product->id">
                        <flux:table.cell>
                            @if ($product->imageUrl())
                                <img src="{{ $product->imageUrl() }}" alt="" class="h-10 w-10 rounded object-cover" />
                            @else
                                <div class="flex h-10 w-10 items-center justify-center rounded bg-zinc-100 text-zinc-400 dark:bg-zinc-800">
                                    <flux:icon name="photo" class="h-5 w-5" />
                                </div>
                            @endif
                        </flux:table.cell>
                        <flux:table.cell>{{ $product->sku }}</flux:table.cell>
                        <flux:table.cell>
                            <flux:link :href="route('products.show', ['productId' => $product->id])" wire:navigate class="font-medium">
                                {{ $product->name }}
                            </flux:link>
                        </flux:table.cell>
                        <flux:table.cell>{{ $product->barcode ?? '—' }}</flux:table.cell>
                        <flux:table.cell align="end">
                            @if($product->isLowStock())
                                <flux:badge color="red">{{ $product->stock }}</flux:badge>
                            @else
                                {{ $product->stock }}
                            @endif
                        </flux:table.cell>
                        <flux:table.cell align="end">{{ number_format($product->sell_price, 0, ',', '.') }}</flux:table.cell>
                        <flux:table.cell align="end">
                            <flux:badge :color="$product->is_active ? 'green' : 'zinc'">
                                {{ $product->is_active ? __('Active') : __('Inactive') }}
                            </flux:badge>
                        </flux:table.cell>
                        <flux:table.cell>
                            <flux:dropdown>
                                <flux:button size="sm" variant="ghost" icon="ellipsis-horizontal" />
                                <flux:menu>
                                    <flux:menu.item icon="eye" :href="route('products.show', ['productId' => $product->id])" wire:navigate>
                                        {{ __('View') }}
                                    </flux:menu.item>
                                    <flux:menu.item icon="pencil" :href="route('products.edit', ['productId' => $product->id])" wire:navigate>
                                        {{ __('Edit') }}
                                    </flux:menu.item>
                                    <flux:menu.item icon="archive-box" wire:click="openAdjustStock({{ $product->id }})">
                                        {{ __('Adjust Stock') }}
                                    </flux:menu.item>
                                </flux:menu>
                            </flux:dropdown>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="8" class="text-center text-zinc-500 dark:text-zinc-400">
                            {{ __('No products found.') }}
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>

        @if ($this->products->hasPages())
            <div class="mt-4">
                {{ $this->products->links() }}
            </div>
        @endif
        </div>

        @livewire(\App\Domains\Product\Livewire\StockAdjustment::class)
</div>
