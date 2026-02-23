<div>
    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl">
        <flux:heading size="xl">{{ __('Admin Dashboard') }}</flux:heading>
        <flux:text class="text-zinc-500 dark:text-zinc-400">
            {{ __('Overview of tenants and subscriptions.') }}
        </flux:text>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <flux:card>
                <flux:text class="text-zinc-500 dark:text-zinc-400">{{ __('Total Tenants') }}</flux:text>
                <flux:heading size="xl">{{ $this->adminStats['total_tenants'] }}</flux:heading>
            </flux:card>
            <flux:card>
                <flux:text class="text-zinc-500 dark:text-zinc-400">{{ __('Active Subscriptions') }}</flux:text>
                <flux:heading size="xl">{{ $this->adminStats['active_subscriptions'] }}</flux:heading>
            </flux:card>
            <flux:card>
                <flux:text class="text-zinc-500 dark:text-zinc-400">{{ __('Trial') }}</flux:text>
                <flux:heading size="xl">{{ $this->adminStats['trial'] }}</flux:heading>
            </flux:card>
            <flux:card>
                <flux:text class="text-zinc-500 dark:text-zinc-400">{{ __('Expired') }}</flux:text>
                <flux:heading size="xl">{{ $this->adminStats['expired'] }}</flux:heading>
            </flux:card>
        </div>

        <flux:card>
            <div class="mb-4 flex items-center justify-between">
                <flux:heading size="lg">{{ __('Tenants & subscriptions') }}</flux:heading>
                <flux:button variant="primary" size="sm" :href="route('admin.tenants')" wire:navigate>
                    {{ __('Manage tenants') }}
                </flux:button>
            </div>
            <flux:table>
                <flux:table.columns>
                    <flux:table.row>
                        <flux:table.cell variant="strong">{{ __('Tenant') }}</flux:table.cell>
                        <flux:table.cell variant="strong">{{ __('Slug') }}</flux:table.cell>
                        <flux:table.cell variant="strong" align="end">{{ __('Users') }}</flux:table.cell>
                        <flux:table.cell variant="strong">{{ __('Subscription') }}</flux:table.cell>
                        <flux:table.cell variant="strong">{{ __('Ends at') }}</flux:table.cell>
                    </flux:table.row>
                </flux:table.columns>
                <flux:table.rows>
                    @forelse ($this->tenantsWithSubscription as $t)
                        @php
                            $sub = $t->subscriptions->first();
                            $isActive = $sub && $sub->ends_at >= now() && in_array($sub->status, [\App\Domains\Subscription\Models\Subscription::STATUS_TRIAL, \App\Domains\Subscription\Models\Subscription::STATUS_ACTIVE]);
                        @endphp
                        <flux:table.row :key="$t->id">
                            <flux:table.cell>{{ $t->name }}</flux:table.cell>
                            <flux:table.cell>{{ $t->slug }}</flux:table.cell>
                            <flux:table.cell align="end">{{ $t->users_count }}</flux:table.cell>
                            <flux:table.cell>
                                @if($sub)
                                    <flux:badge :color="$isActive ? 'green' : 'red'">
                                        {{ ucfirst($sub->status) }}
                                    </flux:badge>
                                @else
                                    <flux:badge color="zinc">—</flux:badge>
                                @endif
                            </flux:table.cell>
                            <flux:table.cell>{{ $sub?->ends_at?->format('d/m/Y') ?? '—' }}</flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="5" class="text-center text-zinc-500 dark:text-zinc-400">
                                {{ __('No tenants yet.') }}
                            </flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>
        </flux:card>
    </div>
</div>
