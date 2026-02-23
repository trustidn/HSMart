<div>
    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl">
        <div class="flex items-center gap-2">
            <flux:link :href="route('admin.users')" wire:navigate icon="arrow-left" icon-position="left">
                {{ __('User Management') }}
            </flux:link>
        </div>

        @if (session('message'))
            <flux:callout variant="success">{{ session('message') }}</flux:callout>
        @endif

        <flux:heading size="xl">{{ $userId ? __('Edit User') : __('New User') }}</flux:heading>

        <form wire:submit="save" class="max-w-2xl space-y-6">
            <flux:field>
                <flux:label>{{ __('Name') }}</flux:label>
                <flux:input wire:model="name" required />
                <flux:error name="name" />
            </flux:field>
            <flux:field>
                <flux:label>{{ __('Email') }}</flux:label>
                <flux:input type="email" wire:model="email" required />
                <flux:error name="email" />
            </flux:field>
            <flux:field>
                <flux:label>{{ $userId ? __('Password (leave blank to keep current)') : __('Password') }}</flux:label>
                <flux:input type="password" wire:model="password" autocomplete="new-password" />
                <flux:error name="password" />
            </flux:field>
            @if(!$userId)
                <flux:field>
                    <flux:label>{{ __('Confirm password') }}</flux:label>
                    <flux:input type="password" wire:model="password_confirmation" autocomplete="new-password" />
                </flux:field>
            @endif
            <flux:field>
                <flux:label>{{ __('Tenant') }}</flux:label>
                <flux:select wire:model="tenant_id" placeholder="{{ __('Select tenant (optional)') }}">
                    <option value="">{{ __('— No tenant (Superadmin) —') }}</option>
                    @foreach($this->tenants as $t)
                        <option value="{{ $t->id }}">{{ $t->name }}</option>
                    @endforeach
                </flux:select>
                <flux:error name="tenant_id" />
            </flux:field>
            <flux:field>
                <flux:checkbox wire:model="is_tenant_owner" :label="__('Tenant owner')" />
                <flux:description>{{ __('Only one owner per tenant. Required tenant above.') }}</flux:description>
                <flux:error name="is_tenant_owner" />
            </flux:field>
            <div class="flex gap-3">
                <flux:button type="submit" variant="primary">{{ __('Save') }}</flux:button>
                <flux:button type="button" variant="ghost" :href="route('admin.users')" wire:navigate>
                    {{ __('Cancel') }}
                </flux:button>
            </div>
        </form>
    </div>
</div>
