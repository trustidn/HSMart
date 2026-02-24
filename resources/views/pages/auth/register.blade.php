<x-layouts::auth>
    <div class="flex flex-col gap-6">
        <x-auth-header :title="'Buat akun'" :description="'Isi data di bawah untuk membuat akun'" />

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('register.store') }}" class="flex flex-col gap-6">
            @csrf
            <!-- Name -->
            <flux:input
                name="name"
                :label="'Nama'"
                :value="old('name')"
                type="text"
                required
                autofocus
                autocomplete="name"
                :placeholder="'Nama lengkap'"
            />

            <!-- Email Address -->
            <flux:input
                name="email"
                :label="'Alamat email'"
                :value="old('email')"
                type="email"
                required
                autocomplete="email"
                placeholder="email@example.com"
            />

            <!-- Password -->
            <flux:input
                name="password"
                :label="'Kata sandi'"
                type="password"
                required
                autocomplete="new-password"
                :placeholder="'Kata sandi'"
                viewable
            />

            <!-- Confirm Password -->
            <flux:input
                name="password_confirmation"
                :label="'Konfirmasi kata sandi'"
                type="password"
                required
                autocomplete="new-password"
                :placeholder="'Konfirmasi kata sandi'"
                viewable
            />

            <div class="flex items-center justify-end">
                <flux:button type="submit" variant="primary" class="w-full auth-btn-primary" data-test="register-user-button">
                    {{ 'Buat akun' }}
                </flux:button>
            </div>
        </form>

        <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-zinc-600 dark:text-zinc-400">
            <span>{{ 'Sudah punya akun?' }}</span>
            <flux:link :href="route('login')" wire:navigate>{{ 'Masuk' }}</flux:link>
        </div>
    </div>
</x-layouts::auth>
