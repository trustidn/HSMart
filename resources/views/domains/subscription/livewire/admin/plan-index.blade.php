<div>
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <flux:heading size="xl">{{ __('Subscription Plans') }}</flux:heading>
                <flux:subheading>{{ __('Paket langganan (1 bulan, 3 bulan, 6 bulan, 1 tahun, dll.)') }}</flux:subheading>
            </div>
            <flux:button variant="primary" icon="plus" :href="route('admin.plans.create')" wire:navigate>
                {{ __('New Plan') }}
            </flux:button>
        </div>

        @if (session('message'))
            <flux:callout variant="success">{{ session('message') }}</flux:callout>
        @endif

        <flux:table>
            <flux:table.columns>
                <flux:table.row>
                    <flux:table.cell variant="strong">{{ __('Name') }}</flux:table.cell>
                    <flux:table.cell variant="strong">{{ __('Duration') }}</flux:table.cell>
                    <flux:table.cell variant="strong" align="end">{{ __('Price') }}</flux:table.cell>
                    <flux:table.cell variant="strong">{{ __('Status') }}</flux:table.cell>
                    <flux:table.cell variant="strong" class="w-0"></flux:table.cell>
                </flux:table.row>
            </flux:table.columns>
            <flux:table.rows>
                @forelse ($this->plans as $plan)
                    <flux:table.row :key="$plan->id">
                        <flux:table.cell>{{ $plan->name }}</flux:table.cell>
                        <flux:table.cell>{{ $plan->duration_months }} {{ __('months') }}</flux:table.cell>
                        <flux:table.cell align="end">{{ number_format($plan->price, 0, ',', '.') }}</flux:table.cell>
                        <flux:table.cell>
                            <flux:badge :color="$plan->is_active ? 'green' : 'zinc'">
                                {{ $plan->is_active ? __('Active') : __('Inactive') }}
                            </flux:badge>
                        </flux:table.cell>
                        <flux:table.cell>
                            <flux:button size="sm" variant="ghost" icon="pencil" :href="route('admin.plans.edit', ['planId' => $plan->id])" wire:navigate>
                                {{ __('Edit') }}
                            </flux:button>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="5" class="text-center text-zinc-500 dark:text-zinc-400">
                            {{ __('No plans yet. Create a plan to offer to tenants.') }}
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>

        @if ($this->plans->hasPages())
            <div class="mt-4">
                {{ $this->plans->links() }}
            </div>
        @endif
    </div>
</div>
