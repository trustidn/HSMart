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
                    <flux:navbar.item class="max-lg:hidden" icon="user" :href="route('users.edit', ['userId' => auth()->id()])" :label="'Ubah profil'" wire:navigate />
                    <flux:navbar.item class="max-lg:hidden" icon="paint-brush" :href="route('settings.white-label')" :label="'Pengaturan toko'" wire:navigate />
                @else
                    <flux:navbar.item class="max-lg:hidden" icon="cog-6-tooth" :href="route('profile.edit')" :label="'Pengaturan'" wire:navigate />
                @endif
                <flux:dropdown position="bottom" align="end">
                    <flux:button variant="ghost" icon="sun" icon:trailing="chevron-down" class="h-8 px-2.5" aria-label="Tampilan" />
                    <flux:menu>
                        <flux:menu.item as="button" type="button" icon="sun" @click="$flux.appearance = 'light'">{{ 'Terang' }}</flux:menu.item>
                        <flux:menu.item as="button" type="button" icon="moon" @click="$flux.appearance = 'dark'">{{ 'Gelap' }}</flux:menu.item>
                        <flux:menu.item as="button" type="button" icon="computer-desktop" @click="$flux.appearance = 'system'">{{ 'Sistem' }}</flux:menu.item>
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
                            {{ 'Ubah profil' }}
                        </flux:menu.item>
                    @else
                        <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>
                            {{ 'Pengaturan' }}
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
                            {{ 'Keluar' }}
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
                        {{ 'Beranda' }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="shopping-cart" :href="route('pos')" :current="request()->routeIs('pos')" wire:navigate>
                        {{ 'Kasir' }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="cube" :href="route('products.index')" :current="request()->routeIs('products.*')" wire:navigate>
                        {{ 'Produk' }}
                    </flux:sidebar.item>
                    <flux:sidebar.group :heading="'Pembelian'" icon="truck" expandable>
                        <flux:sidebar.item icon="truck" :href="route('purchasing.purchases.index')" :current="request()->routeIs('purchasing.purchases.*')" wire:navigate>
                            {{ 'Pembelian' }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="building-storefront" :href="route('purchasing.suppliers.index')" :current="request()->routeIs('purchasing.suppliers.*')" wire:navigate>
                            {{ 'Pemasok' }}
                        </flux:sidebar.item>
                    </flux:sidebar.group>
                    <flux:sidebar.item icon="chart-bar" :href="route('reports')" :current="request()->routeIs('reports')" wire:navigate>
                        {{ 'Laporan' }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="paint-brush" :href="route('settings.white-label')" :current="request()->routeIs('settings.white-label')" wire:navigate>
                        {{ 'Pengaturan toko' }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="credit-card" :href="route('subscription.index')" :current="request()->routeIs('subscription.index')" wire:navigate>
                        {{ 'Langganan' }}
                    </flux:sidebar.item>
                    @if(auth()->user()->isTenantOwner())
                        <flux:sidebar.item icon="users" :href="route('users.index')" :current="request()->routeIs('users.*')" wire:navigate>
                            {{ 'Pengguna' }}
                        </flux:sidebar.item>
                    @endif
                @else
                    <flux:sidebar.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
                        {{ 'Beranda' }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="cog-6-tooth" :href="route('admin.platform-settings')" :current="request()->routeIs('admin.platform-settings')" wire:navigate>
                        {{ 'Pengaturan Platform' }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="building-office-2" :href="route('admin.tenants')" :current="request()->routeIs('admin.tenants*')" wire:navigate>
                        {{ 'Kelola Tenant' }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="credit-card" :href="route('admin.plans')" :current="request()->routeIs('admin.plans*')" wire:navigate>
                        {{ 'Paket Langganan' }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="users" :href="route('admin.users')" :current="request()->routeIs('admin.users')" wire:navigate>
                        {{ 'Kelola Pengguna' }}
                    </flux:sidebar.item>
                @endif
            </flux:sidebar.nav>

            <flux:spacer />
        </flux:sidebar>

        @if(tenant() && !app(\App\Domains\Subscription\Services\SubscriptionService::class)->isActive(tenant()))
            <div class="sticky top-0 z-10 mx-4 mt-4">
                <flux:callout variant="warning" icon="exclamation-triangle">
                    {{ 'Langganan Anda telah berakhir. Anda dapat melihat data tetapi tidak dapat membuat penjualan atau pembelian baru.' }}
                    <flux:link :href="route('subscription.expired')" wire:navigate class="font-medium">{{ 'Detail' }}</flux:link>
                </flux:callout>
            </div>
        @endif

        {{ $slot }}

        @fluxScripts
    </body>
</html>
