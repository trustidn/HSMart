<div>
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <flux:heading size="xl">{{ 'Paket Langganan' }}</flux:heading>
                <flux:subheading>{{ 'Paket langganan (1 bulan, 3 bulan, 6 bulan, 1 tahun, dll.)' }}</flux:subheading>
            </div>
            <flux:button variant="primary" icon="plus" :href="route('admin.plans.create')" wire:navigate>
                {{ 'Paket Baru' }}
            </flux:button>
        </div>

        @if (session('message'))
            <flux:callout variant="success">{{ session('message') }}</flux:callout>
        @endif

        <flux:table>
            <flux:table.columns>
                <flux:table.row>
                    <flux:table.cell variant="strong">{{ 'Nama' }}</flux:table.cell>
                    <flux:table.cell variant="strong">{{ 'Durasi' }}</flux:table.cell>
                    <flux:table.cell variant="strong" align="end">{{ 'Harga' }}</flux:table.cell>
                    <flux:table.cell variant="strong">{{ 'Status' }}</flux:table.cell>
                    <flux:table.cell variant="strong" class="w-0"></flux:table.cell>
                </flux:table.row>
            </flux:table.columns>
            <flux:table.rows>
                @forelse ($this->plans as $plan)
                    <flux:table.row :key="$plan->id">
                        <flux:table.cell>{{ $plan->name }}</flux:table.cell>
                        <flux:table.cell>{{ $plan->duration_months }} {{ 'bulan' }}</flux:table.cell>
                        <flux:table.cell align="end">{{ number_format($plan->price, 0, ',', '.') }}</flux:table.cell>
                        <flux:table.cell>
                            <flux:badge :color="$plan->is_active ? 'green' : 'zinc'">
                                {{ $plan->is_active ? 'Aktif' : 'Nonaktif' }}
                            </flux:badge>
                        </flux:table.cell>
                        <flux:table.cell>
                            <flux:button size="sm" variant="ghost" icon="pencil" :href="route('admin.plans.edit', ['planId' => $plan->id])" wire:navigate>
                                {{ 'Ubah' }}
                            </flux:button>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="5" class="text-center text-zinc-500 dark:text-zinc-400">
                            {{ 'Belum ada paket. Buat paket untuk ditawarkan ke tenant.' }}
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
