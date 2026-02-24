<div>
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <flux:heading size="xl">{{ 'Pengaturan Platform' }}</flux:heading>
        <flux:text class="text-zinc-500 dark:text-zinc-400">
            {{ 'Nama aplikasi, logo, dan warna dasar untuk halaman muka dan login.' }}
        </flux:text>

        <form wire:submit="save" class="max-w-2xl space-y-6">
            <flux:field>
                <flux:label>{{ 'Nama aplikasi' }}</flux:label>
                <flux:input wire:model="app_name" required />
                <flux:error name="app_name" />
            </flux:field>

            <flux:field>
                <flux:label>{{ 'Logo' }}</flux:label>
                @php
                    $currentLogoUrl = \App\Domains\Platform\Models\PlatformSetting::current()->logoUrl();
                @endphp
                @if($currentLogoUrl)
                    <div class="flex items-center gap-4">
                        <img src="{{ $currentLogoUrl }}" alt="" class="h-16 w-auto rounded object-contain border border-zinc-200 dark:border-zinc-700" />
                        <flux:button type="button" variant="ghost" size="sm" wire:click="removeLogo">
                            {{ 'Hapus logo' }}
                        </flux:button>
                    </div>
                @endif
                <flux:input type="file" wire:model="logo" accept="image/*" class="mt-2" />
                <flux:error name="logo" />
            </flux:field>

            <div class="grid gap-6 sm:grid-cols-2">
                <flux:field>
                    <flux:label>{{ 'Warna dasar (utama)' }}</flux:label>
                    <flux:input type="color" wire:model="primary_color" class="h-10 w-full cursor-pointer p-1" />
                    <flux:input type="text" wire:model="primary_color" placeholder="#0f766e" class="mt-1 font-mono text-sm" />
                    <flux:error name="primary_color" />
                </flux:field>
                <flux:field>
                    <flux:label>{{ 'Warna sekunder' }}</flux:label>
                    <flux:input type="color" wire:model="secondary_color" class="h-10 w-full cursor-pointer p-1" />
                    <flux:input type="text" wire:model="secondary_color" placeholder="#134e4a" class="mt-1 font-mono text-sm" />
                    <flux:error name="secondary_color" />
                </flux:field>
            </div>

            <div class="flex gap-3">
                <flux:button type="submit" variant="primary">
                    {{ 'Simpan pengaturan' }}
                </flux:button>
                <flux:button type="button" variant="ghost" :href="route('dashboard')" wire:navigate>
                    {{ 'Batal' }}
                </flux:button>
            </div>
        </form>
    </div>
</div>
