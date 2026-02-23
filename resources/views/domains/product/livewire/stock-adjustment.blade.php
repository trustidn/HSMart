<div>
    @if ($showModal && $this->product)
        <flux:modal name="stock-adjustment" :show="$showModal" class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Adjust Stock') }}</flux:heading>
                <flux:subheading>{{ $this->product->name }} ({{ $this->product->sku }})</flux:subheading>
            </div>

            <flux:field>
                <flux:label>{{ __('New quantity') }}</flux:label>
                <flux:input type="number" wire:model="newQuantity" min="0" required />
                <flux:error name="newQuantity" />
            </flux:field>

            <div class="flex justify-end gap-2">
                <flux:button variant="ghost" wire:click="closeModal">
                    {{ __('Cancel') }}
                </flux:button>
                <flux:button variant="primary" wire:click="adjust">
                    {{ __('Save') }}
                </flux:button>
            </div>
        </flux:modal>
    @endif
</div>
