<div>
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
            <div class="flex items-center gap-2">
                <flux:link :href="route('purchasing.suppliers.index')" wire:navigate icon="arrow-left" icon-position="left">
                    {{ __('Suppliers') }}
                </flux:link>
            </div>
            <flux:heading size="xl">{{ $supplierId ? __('Edit Supplier') : __('New Supplier') }}</flux:heading>

            <form wire:submit="save" class="max-w-2xl space-y-6">
                <flux:field>
                    <flux:label>{{ __('Name') }}</flux:label>
                    <flux:input wire:model="name" required />
                    <flux:error name="name" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Contact') }}</flux:label>
                    <flux:input wire:model="contact" />
                    <flux:error name="contact" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Phone') }}</flux:label>
                    <flux:input wire:model="phone" />
                    <flux:error name="phone" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Address') }}</flux:label>
                    <flux:textarea wire:model="address" rows="3" />
                    <flux:error name="address" />
                </flux:field>

                <div class="flex gap-3">
                    <flux:button type="submit" variant="primary">
                        {{ __('Save') }}
                    </flux:button>
                    <flux:button type="button" variant="ghost" :href="route('purchasing.suppliers.index')" wire:navigate>
                        {{ __('Cancel') }}
                    </flux:button>
                </div>
            </form>
        </div>
</div>
