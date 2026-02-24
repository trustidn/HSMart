<div>
    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl">
        <div class="flex flex-col gap-2">
            <flux:heading size="4xl">{{ $this->greeting }}</flux:heading>
            <flux:heading size="xl" class="font-normal text-zinc-500 dark:text-zinc-400">
                {{ $this->currentDate }}
            </flux:heading>
        </div>

        <flux:card>
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div class="flex flex-col gap-1">
                    <flux:text class="text-zinc-500 dark:text-zinc-400">{{ __('Current plan') }}</flux:text>
                    @if ($this->tenantSubscription)
                        <flux:heading size="lg">{{ $this->tenantSubscription->plan?->name ?? __('Subscription') }}</flux:heading>
                        <div class="mt-2 flex flex-wrap items-center gap-2">
                            <flux:badge :color="$this->tenantSubscription->status === \App\Domains\Subscription\Models\Subscription::STATUS_ACTIVE ? 'green' : 'zinc'">
                                {{ ucfirst($this->tenantSubscription->status) }}
                            </flux:badge>
                            <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">
                                {{ __('Valid until') }} {{ $this->tenantSubscription->ends_at?->format('d/m/Y') }}
                            </flux:text>
                        </div>
                    @else
                        <flux:heading size="lg">{{ __('No active subscription') }}</flux:heading>
                        <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">
                            {{ __('Renew or choose a plan to continue using all features.') }}
                        </flux:text>
                    @endif
                </div>
                <flux:button variant="ghost" size="sm" :href="route('subscription.index')" wire:navigate>
                    {{ __('Manage subscription') }}
                </flux:button>
            </div>
        </flux:card>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <flux:card>
                <flux:text class="text-zinc-500 dark:text-zinc-400">{{ __('Total products') }}</flux:text>
                <flux:heading size="xl">{{ number_format($this->tenantStats['total_products'], 0, ',', '.') }}</flux:heading>
                <flux:link :href="route('products.index')" wire:navigate size="sm" class="mt-2">
                    {{ __('View products') }}
                </flux:link>
            </flux:card>
            <flux:card>
                <flux:text class="text-zinc-500 dark:text-zinc-400">{{ __('Low stock items') }}</flux:text>
                <flux:heading size="xl">{{ number_format($this->tenantStats['low_stock_count'], 0, ',', '.') }}</flux:heading>
                @if ($this->tenantStats['low_stock_count'] > 0)
                    <flux:badge color="amber" class="mt-2">{{ __('Needs attention') }}</flux:badge>
                @endif
            </flux:card>
            <flux:card>
                <flux:text class="text-zinc-500 dark:text-zinc-400">{{ __('Sales today') }}</flux:text>
                <flux:heading size="xl">{{ number_format($this->tenantStats['today_sales_count'], 0, ',', '.') }}</flux:heading>
                <flux:text class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                    {{ __('transactions') }}
                </flux:text>
            </flux:card>
            <flux:card>
                <flux:text class="text-zinc-500 dark:text-zinc-400">{{ __('Revenue today') }}</flux:text>
                <flux:heading size="xl">Rp {{ number_format($this->tenantStats['today_revenue'], 0, ',', '.') }}</flux:heading>
                <flux:link :href="route('reports')" wire:navigate size="sm" class="mt-2">
                    {{ __('View reports') }}
                </flux:link>
            </flux:card>
        </div>

        <flux:card>
            <div class="mb-4 flex items-center justify-between">
                <flux:heading size="lg">{{ __('Recent transactions') }}</flux:heading>
                <flux:button variant="primary" size="sm" :href="route('pos')" wire:navigate icon="plus">
                    {{ __('New sale') }}
                </flux:button>
            </div>
            <flux:table>
                <flux:table.columns>
                    <flux:table.row>
                        <flux:table.cell variant="strong">{{ __('Sale number') }}</flux:table.cell>
                        <flux:table.cell variant="strong">{{ __('Date') }}</flux:table.cell>
                        <flux:table.cell variant="strong" align="end">{{ __('Total') }}</flux:table.cell>
                    </flux:table.row>
                </flux:table.columns>
                <flux:table.rows>
                    @forelse ($this->recentSales as $sale)
                        <flux:table.row :key="$sale->id">
                            <flux:table.cell>{{ $sale->sale_number }}</flux:table.cell>
                            <flux:table.cell>{{ $sale->sale_date?->format('d/m/Y') ?? '—' }}</flux:table.cell>
                            <flux:table.cell align="end">Rp {{ number_format($sale->total_amount, 0, ',', '.') }}</flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="3" class="text-center text-zinc-500 dark:text-zinc-400">
                                {{ __('No sales yet.') }}
                            </flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>
        </flux:card>
    </div>
</div>
