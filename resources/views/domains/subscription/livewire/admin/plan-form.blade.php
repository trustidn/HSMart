<div>
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="flex items-center gap-2">
            <flux:link :href="route('admin.plans')" wire:navigate icon="arrow-left" icon-position="left">
                {{ __('Subscription Plans') }}
            </flux:link>
        </div>
        <flux:heading size="xl">{{ $planId ? __('Edit Plan') : __('New Plan') }}</flux:heading>

        <form wire:submit="save" class="max-w-2xl space-y-6">
            <flux:field>
                <flux:label>{{ __('Name') }}</flux:label>
                <flux:input wire:model="name" :placeholder="__('e.g. 1 Bulan, 3 Bulan, 1 Tahun')" required />
                <flux:error name="name" />
            </flux:field>

            <flux:field>
                <flux:label>{{ __('Duration (months)') }}</flux:label>
                <flux:input type="number" wire:model="duration_months" min="1" max="120" required />
                <flux:error name="duration_months" />
            </flux:field>

            <flux:field>
                <flux:label>{{ __('Price') }}</flux:label>
                <flux:input type="number" wire:model="price" min="0" step="0.01" required />
                <flux:error name="price" />
            </flux:field>

            <flux:field>
                <flux:checkbox wire:model="is_active" :label="__('Active (visible to tenants)')" />
            </flux:field>

            <div class="flex gap-3">
                <flux:button type="submit" variant="primary">{{ __('Save') }}</flux:button>
                <flux:button type="button" variant="ghost" :href="route('admin.plans')" wire:navigate>
                    {{ __('Cancel') }}
                </flux:button>
            </div>
        </form>
    </div>
</div>
