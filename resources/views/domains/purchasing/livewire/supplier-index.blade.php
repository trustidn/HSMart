<div>
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <flux:heading size="xl">{{ 'Pemasok' }}</flux:heading>
                <flux:button variant="primary" icon="plus" :href="route('purchasing.suppliers.create')" wire:navigate>
                    {{ 'Pemasok Baru' }}
                </flux:button>
            </div>

            <flux:field>
                <flux:input wire:model.live.debounce.300ms="search" :placeholder="'Cari nama, kontak, telepon...'" icon="magnifying-glass" />
            </flux:field>

            <flux:table>
                <flux:table.columns>
                    <flux:table.row>
                        <flux:table.cell variant="strong">{{ 'Nama' }}</flux:table.cell>
                        <flux:table.cell variant="strong">{{ 'Kontak' }}</flux:table.cell>
                        <flux:table.cell variant="strong">{{ 'Telepon' }}</flux:table.cell>
                        <flux:table.cell variant="strong">{{ 'Alamat' }}</flux:table.cell>
                        <flux:table.cell variant="strong" class="w-0"></flux:table.cell>
                    </flux:table.row>
                </flux:table.columns>
                <flux:table.rows>
                    @forelse ($this->suppliers as $supplier)
                        <flux:table.row :key="$supplier->id">
                            <flux:table.cell>{{ $supplier->name }}</flux:table.cell>
                            <flux:table.cell>{{ $supplier->contact ?? '—' }}</flux:table.cell>
                            <flux:table.cell>{{ $supplier->phone ?? '—' }}</flux:table.cell>
                            <flux:table.cell>{{ \Illuminate\Support\Str::limit($supplier->address, 40) ?: '—' }}</flux:table.cell>
                            <flux:table.cell>
                                <flux:button size="sm" variant="ghost" icon="pencil" :href="route('purchasing.suppliers.edit', ['supplierId' => $supplier->id])" wire:navigate>
                                    {{ 'Ubah' }}
                                </flux:button>
                            </flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="5" class="text-center text-zinc-500 dark:text-zinc-400">
                                {{ 'Tidak ada pemasok.' }}
                            </flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>

            @if ($this->suppliers->hasPages())
                <div class="mt-4">
                    {{ $this->suppliers->links() }}
                </div>
            @endif
        </div>
</div>
