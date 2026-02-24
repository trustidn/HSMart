<div>
    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl">
        <div class="flex items-center gap-2">
            <flux:link :href="route('admin.users')" wire:navigate icon="arrow-left" icon-position="left">
                {{ 'Manajemen Pengguna' }}
            </flux:link>
        </div>

        @if (session('message'))
            <flux:callout variant="success">{{ session('message') }}</flux:callout>
        @endif

        <flux:heading size="xl">{{ $userId ? 'Ubah Pengguna' : 'Pengguna Baru' }}</flux:heading>

        <form wire:submit="save" class="max-w-2xl space-y-6">
            <flux:field>
                <flux:label>{{ 'Nama' }}</flux:label>
                <flux:input wire:model="name" required />
                <flux:error name="name" />
            </flux:field>
            <flux:field>
                <flux:label>{{ 'Email' }}</flux:label>
                <flux:input type="email" wire:model="email" required />
                <flux:error name="email" />
            </flux:field>
            <flux:field>
                <flux:label>{{ $userId ? 'Kata sandi (kosongkan jika tidak diubah)' : 'Kata sandi' }}</flux:label>
                <flux:input type="password" wire:model="password" autocomplete="new-password" />
                <flux:error name="password" />
            </flux:field>
            @if(!$userId)
                <flux:field>
                    <flux:label>{{ 'Konfirmasi kata sandi' }}</flux:label>
                    <flux:input type="password" wire:model="password_confirmation" autocomplete="new-password" />
                </flux:field>
            @endif
            <flux:field>
                <flux:label>{{ 'Tenant' }}</flux:label>
                <flux:select wire:model="tenant_id" placeholder="Pilih tenant (opsional)">
                    <option value="">— Tanpa tenant (Superadmin) —</option>
                    @foreach($this->tenants as $t)
                        <option value="{{ $t->id }}">{{ $t->name }}</option>
                    @endforeach
                </flux:select>
                <flux:error name="tenant_id" />
            </flux:field>
            <flux:field>
                <flux:checkbox wire:model="is_tenant_owner" :label="'Pemilik tenant'" />
                <flux:description>{{ 'Satu pemilik per tenant. Wajib pilih tenant di atas.' }}</flux:description>
                <flux:error name="is_tenant_owner" />
            </flux:field>
            <div class="flex gap-3">
                <flux:button type="submit" variant="primary">{{ 'Simpan' }}</flux:button>
                <flux:button type="button" variant="ghost" :href="route('admin.users')" wire:navigate>
                    {{ 'Batal' }}
                </flux:button>
            </div>
        </form>
    </div>
</div>
