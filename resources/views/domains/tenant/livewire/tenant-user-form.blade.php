<div>
    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl">
        <div class="flex items-center gap-2">
            @if($isEditingSelf ?? false)
                <flux:link :href="auth()->user()->isTenantOwner() ? route('users.index') : route('dashboard')" wire:navigate icon="arrow-left" icon-position="left">
                    {{ auth()->user()->isTenantOwner() ? 'Pengguna' : 'Beranda' }}
                </flux:link>
            @else
                <flux:link :href="route('users.index')" wire:navigate icon="arrow-left" icon-position="left">
                    {{ 'Pengguna' }}
                </flux:link>
            @endif
        </div>

        @if (session('message'))
            <flux:callout variant="success">{{ session('message') }}</flux:callout>
        @endif

        <flux:heading size="xl">{{ $userId ? ($isEditingSelf ?? false ? 'Ubah profil' : 'Ubah Pengguna') : 'Pengguna Baru' }}</flux:heading>

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
                <flux:label>{{ $userId ? 'Kata sandi (kosongkan untuk tetap pakai yang sekarang)' : 'Kata sandi' }}</flux:label>
                <flux:input type="password" wire:model="password" autocomplete="new-password" />
                <flux:error name="password" />
            </flux:field>
            <flux:field>
                <flux:label>{{ $userId ? 'Konfirmasi kata sandi (jika mengubah)' : 'Konfirmasi kata sandi' }}</flux:label>
                <flux:input type="password" wire:model="password_confirmation" autocomplete="new-password" id="password_confirmation" />
                <flux:error name="password_confirmation" />
            </flux:field>
            <div class="flex gap-3">
                <flux:button type="submit" variant="primary">{{ 'Simpan' }}</flux:button>
                @if($isEditingSelf ?? false)
                    <flux:button type="button" variant="ghost" :href="auth()->user()->isTenantOwner() ? route('users.index') : route('dashboard')" wire:navigate>
                        {{ 'Batal' }}
                    </flux:button>
                @else
                    <flux:button type="button" variant="ghost" :href="route('users.index')" wire:navigate>
                        {{ 'Batal' }}
                    </flux:button>
                @endif
            </div>
        </form>
    </div>
</div>
