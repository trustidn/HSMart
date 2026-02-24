<div>
    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl">
        <div class="flex items-center gap-2">
            <flux:link :href="route('admin.tenants')" wire:navigate icon="arrow-left" icon-position="left">
                {{ 'Manajemen Tenant' }}
            </flux:link>
        </div>

        @if (session('message'))
            <flux:callout variant="success">{{ session('message') }}</flux:callout>
        @endif

        <flux:heading size="xl">{{ $tenantId ? 'Ubah Tenant' : 'Tenant Baru' }}</flux:heading>

        <form wire:submit="save" class="max-w-2xl space-y-6">
            <flux:field>
                <flux:label>{{ 'Nama' }}</flux:label>
                <flux:input wire:model="name" required />
                <flux:error name="name" />
            </flux:field>
            @if($tenantId)
                <flux:field>
                    <flux:label>{{ 'Slug' }}</flux:label>
                    <flux:input wire:model="slug" placeholder="tenant-slug" />
                    <flux:error name="slug" />
                </flux:field>
            @endif
            <div class="flex gap-3">
                <flux:button type="submit" variant="primary">{{ 'Simpan' }}</flux:button>
                <flux:button type="button" variant="ghost" :href="route('admin.tenants')" wire:navigate>
                    {{ 'Batal' }}
                </flux:button>
            </div>
        </form>

        @if($tenantId)
            <flux:separator />
            @livewire(\App\Domains\Tenant\Livewire\Admin\TenantSubscriptionManager::class, ['tenantId' => $tenantId], key('subscription-'.$tenantId))
        @endif
    </div>
</div>
