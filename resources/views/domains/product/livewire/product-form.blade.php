<div>
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
            <div class="flex items-center gap-2">
                <flux:link :href="route('products.index')" wire:navigate icon="arrow-left" icon-position="left">
                    {{ __('Products') }}
                </flux:link>
            </div>
            <flux:heading size="xl">{{ $productId ? __('Edit Product') : __('New Product') }}</flux:heading>

            <form wire:submit="save" class="max-w-2xl space-y-6">
                <flux:field>
                    <flux:label>{{ __('Product photo') }}</flux:label>
                    @if ($this->product?->imageUrl())
                        <div class="mb-2">
                            <img src="{{ $this->product->imageUrl() }}" alt="" class="h-24 w-24 rounded-lg border border-zinc-200 object-cover dark:border-zinc-700" />
                        </div>
                    @endif
                    <flux:input type="file" wire:model="photo" accept="image/*" />
                    <flux:error name="photo" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('SKU') }}</flux:label>
                    <flux:input wire:model="sku" required />
                    <flux:error name="sku" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Name') }}</flux:label>
                    <flux:input wire:model="name" required />
                    <flux:error name="name" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Barcode') }}</flux:label>
                    <flux:input wire:model="barcode" />
                    <flux:error name="barcode" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Sell Price') }}</flux:label>
                    <flux:input type="number" wire:model="sell_price" min="0" step="0.01" required />
                    <flux:error name="sell_price" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Minimum Stock') }}</flux:label>
                    <flux:input type="number" wire:model="minimum_stock" min="0" required />
                    <flux:error name="minimum_stock" />
                </flux:field>

                <flux:field>
                    <flux:checkbox wire:model="is_active" :label="__('Active')" />
                </flux:field>

                <div class="flex gap-3">
                    <flux:button type="submit" variant="primary">
                        {{ __('Save') }}
                    </flux:button>
                    <flux:button type="button" variant="ghost" :href="route('products.index')" wire:navigate>
                        {{ __('Cancel') }}
                    </flux:button>
                </div>
            </form>
        </div>
</div>
