<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
        @if(tenant()?->setting?->primary_color)
            <style>
                :root, .dark { --color-accent: {{ tenant()->setting->primary_color }}; --color-accent-content: {{ tenant()->setting->primary_color }}; }
            </style>
        @endif
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800 antialiased">
        <flux:header sticky class="border-b border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />
            <x-app-logo :href="route('dashboard')" wire:navigate class="max-lg:ms-2" />
            <flux:spacer />
            <flux:navbar class="gap-1">
                @if(tenant())
                    <flux:navbar.item icon="user" :href="route('users.edit', ['userId' => auth()->id()])" :label="__('Edit profile')" wire:navigate />
                    <flux:navbar.item icon="paint-brush" :href="route('settings.white-label')" :label="__('Store settings')" wire:navigate />
                @else
                    <flux:navbar.item icon="cog-6-tooth" :href="route('profile.edit')" :label="__('Settings')" wire:navigate />
                @endif
                <flux:dropdown position="bottom" align="end">
                    <flux:button variant="ghost" icon="sun" icon:trailing="chevron-down" class="h-8 px-2.5">{{ __('Appearance') }}</flux:button>
                    <flux:menu>
                        <flux:menu.item as="button" type="button" icon="sun" @click="$flux.appearance = 'light'">{{ __('Light') }}</flux:menu.item>
                        <flux:menu.item as="button" type="button" icon="moon" @click="$flux.appearance = 'dark'">{{ __('Dark') }}</flux:menu.item>
                        <flux:menu.item as="button" type="button" icon="computer-desktop" @click="$flux.appearance = 'system'">{{ __('System') }}</flux:menu.item>
                    </flux:menu>
                </flux:dropdown>
            </flux:navbar>
            <flux:dropdown position="bottom" align="end">
                <flux:profile
                    :initials="auth()->user()->initials()"
                    icon-trailing="chevron-down"
                />
                <flux:menu>
                    <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                        <flux:avatar
                            :name="auth()->user()->name"
                            :initials="auth()->user()->initials()"
                        />
                        <div class="grid flex-1 text-start text-sm leading-tight">
                            <flux:heading class="truncate">{{ auth()->user()->name }}</flux:heading>
                            <flux:text class="truncate">{{ auth()->user()->email }}</flux:text>
                        </div>
                    </div>
                    <flux:menu.separator />
                    @if(tenant())
                        <flux:menu.item :href="route('users.edit', ['userId' => auth()->id()])" icon="user" wire:navigate>
                            {{ __('Edit profile') }}
                        </flux:menu.item>
                    @else
                        <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>
                            {{ __('Settings') }}
                        </flux:menu.item>
                    @endif
                    <flux:menu.separator />
                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item
                            as="button"
                            type="submit"
                            icon="arrow-right-start-on-rectangle"
                            class="w-full cursor-pointer"
                            data-test="logout-button"
                        >
                            {{ __('Log Out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:header>

        <flux:sidebar sticky collapsible class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.header>
                <flux:sidebar.collapse class="in-data-flux-sidebar-on-desktop:not-in-data-flux-sidebar-collapsed-desktop:-mr-2" />
            </flux:sidebar.header>

            <flux:sidebar.nav>
                @if(tenant())
                    <flux:sidebar.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
                        {{ __('Dashboard') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="shopping-cart" :href="route('pos')" :current="request()->routeIs('pos')" wire:navigate>
                        {{ __('POS') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="cube" :href="route('products.index')" :current="request()->routeIs('products.*')" wire:navigate>
                        {{ __('Products') }}
                    </flux:sidebar.item>
                    <flux:sidebar.group :heading="__('Purchasing')" icon="truck" expandable>
                        <flux:sidebar.item icon="truck" :href="route('purchasing.purchases.index')" :current="request()->routeIs('purchasing.purchases.*')" wire:navigate>
                            {{ __('Purchases') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="building-storefront" :href="route('purchasing.suppliers.index')" :current="request()->routeIs('purchasing.suppliers.*')" wire:navigate>
                            {{ __('Suppliers') }}
                        </flux:sidebar.item>
                    </flux:sidebar.group>
                    <flux:sidebar.item icon="chart-bar" :href="route('reports')" :current="request()->routeIs('reports')" wire:navigate>
                        {{ __('Reports') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="paint-brush" :href="route('settings.white-label')" :current="request()->routeIs('settings.white-label')" wire:navigate>
                        {{ __('Store settings') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="credit-card" :href="route('subscription.index')" :current="request()->routeIs('subscription.index')" wire:navigate>
                        {{ __('Subscription') }}
                    </flux:sidebar.item>
                    @if(auth()->user()->isTenantOwner())
                        <flux:sidebar.item icon="users" :href="route('users.index')" :current="request()->routeIs('users.*')" wire:navigate>
                            {{ __('Users') }}
                        </flux:sidebar.item>
                    @endif
                @else
                    <flux:sidebar.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
                        {{ __('Dashboard') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="building-office-2" :href="route('admin.tenants')" :current="request()->routeIs('admin.tenants*')" wire:navigate>
                        {{ __('Tenant Management') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="credit-card" :href="route('admin.plans')" :current="request()->routeIs('admin.plans*')" wire:navigate>
                        {{ __('Subscription Plans') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="users" :href="route('admin.users')" :current="request()->routeIs('admin.users')" wire:navigate>
                        {{ __('User Management') }}
                    </flux:sidebar.item>
                @endif
            </flux:sidebar.nav>

            <flux:spacer />
        </flux:sidebar>

        @if(tenant() && !app(\App\Domains\Subscription\Services\SubscriptionService::class)->isActive(tenant()))
            <div class="sticky top-0 z-10 mx-4 mt-4">
                <flux:callout variant="warning" icon="exclamation-triangle">
                    {{ __('Your subscription has expired. You can view data but cannot create new sales or purchases.') }}
                    <flux:link :href="route('subscription.expired')" wire:navigate class="font-medium">{{ __('Details') }}</flux:link>
                </flux:callout>
            </div>
        @endif

        {{ $slot }}

        @fluxScripts
    </body>
</html>
