<div>
    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl">
        <div class="flex items-center gap-2">
            @if($isEditingSelf ?? false)
                <flux:link :href="auth()->user()->isTenantOwner() ? route('users.index') : route('dashboard')" wire:navigate icon="arrow-left" icon-position="left">
                    {{ auth()->user()->isTenantOwner() ? __('Users') : __('Dashboard') }}
                </flux:link>
            @else
                <flux:link :href="route('users.index')" wire:navigate icon="arrow-left" icon-position="left">
                    {{ __('Users') }}
                </flux:link>
            @endif
        </div>

        @if (session('message'))
            <flux:callout variant="success">{{ session('message') }}</flux:callout>
        @endif

        <flux:heading size="xl">{{ $userId ? ($isEditingSelf ?? false ? __('Edit profile') : __('Edit User')) : __('New User') }}</flux:heading>

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
            <flux:field>
                <flux:label>{{ __('Confirm password') }}</flux:label>
                <flux:input type="password" wire:model="password_confirmation" autocomplete="new-password" />
                <flux:error name="password_confirmation" />
            </flux:field>
            <div class="flex gap-3">
                <flux:button type="submit" variant="primary">{{ __('Save') }}</flux:button>
                <flux:button type="button" variant="ghost" :href="route('users.index')" wire:navigate>
                    {{ __('Cancel') }}
                </flux:button>
            </div>
        </form>
    </div>
</div>
