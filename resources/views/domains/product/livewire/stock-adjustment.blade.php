<div>
    @if ($showModal && $this->product)
        <div
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
            role="dialog"
            aria-modal="true"
            aria-labelledby="stock-adjustment-title"
        >
            <div
                class="w-full max-w-md rounded-xl border border-zinc-200 bg-white p-6 shadow-xl dark:border-zinc-700 dark:bg-zinc-800"
                wire:click.stop
            >
                <flux:heading size="lg" id="stock-adjustment-title">{{ __('Adjust Stock') }}</flux:heading>
                <flux:subheading class="mt-1">{{ $this->product->name }} ({{ $this->product->sku }})</flux:subheading>

                <flux:field class="mt-4">
                    <flux:label>{{ __('New quantity') }}</flux:label>
                    <flux:input type="number" wire:model="newQuantity" min="0" required />
                    <flux:error name="newQuantity" />
                </flux:field>

                <div class="mt-6 flex justify-end gap-2">
                    <flux:button variant="ghost" wire:click="closeModal">
                        {{ __('Cancel') }}
                    </flux:button>
                    <flux:button variant="primary" wire:click="adjust">
                        {{ __('Save') }}
                    </flux:button>
                </div>
            </div>
        </div>
    @endif
</div>
