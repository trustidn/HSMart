<x-layouts::auth>
    <div class="flex flex-col gap-6">
        <x-auth-header
            :title="'Konfirmasi kata sandi'"
            :description="'Area aman. Silakan konfirmasi kata sandi untuk melanjutkan.'"
        />

        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('password.confirm.store') }}" class="flex flex-col gap-6">
            @csrf

            <flux:input
                name="password"
                :label="'Kata sandi'"
                type="password"
                required
                autocomplete="current-password"
                :placeholder="'Kata sandi'"
                viewable
            />

            <flux:button variant="primary" type="submit" class="w-full" data-test="confirm-password-button">
                {{ 'Konfirmasi' }}
            </flux:button>
        </form>
    </div>
</x-layouts::auth>
