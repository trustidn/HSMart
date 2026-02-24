<div>
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="flex items-center gap-2">
            <flux:link :href="route('admin.plans')" wire:navigate icon="arrow-left" icon-position="left">
                {{ 'Paket Langganan' }}
            </flux:link>
        </div>
        <flux:heading size="xl">{{ $planId ? 'Ubah Paket' : 'Paket Baru' }}</flux:heading>

        <form wire:submit="save" class="max-w-2xl space-y-6">
            <flux:field>
                <flux:label>{{ 'Nama' }}</flux:label>
                <flux:input wire:model="name" :placeholder="'contoh: 1 Bulan, 3 Bulan, 1 Tahun'" required />
                <flux:error name="name" />
            </flux:field>

            <flux:field>
                <flux:label>{{ 'Durasi (bulan)' }}</flux:label>
                <flux:input type="number" wire:model="duration_months" min="1" max="120" required />
                <flux:error name="duration_months" />
            </flux:field>

            <flux:field>
                <flux:label>{{ 'Harga' }}</flux:label>
                <flux:input type="number" wire:model="price" min="0" step="0.01" required />
                <flux:error name="price" />
            </flux:field>

            <flux:field>
                <flux:checkbox wire:model="is_active" :label="'Aktif (terlihat oleh tenant)'" />
            </flux:field>

            <div class="flex gap-3">
                <flux:button type="submit" variant="primary">{{ 'Simpan' }}</flux:button>
                <flux:button type="button" variant="ghost" :href="route('admin.plans')" wire:navigate>
                    {{ 'Batal' }}
                </flux:button>
            </div>
        </form>
    </div>
</div>
