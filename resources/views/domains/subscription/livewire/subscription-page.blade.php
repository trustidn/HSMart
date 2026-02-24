<div>
    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl">
        <flux:heading size="xl">{{ 'Langganan' }}</flux:heading>

        @if (session('subscription_message'))
            <flux:callout variant="success">{{ session('subscription_message') }}</flux:callout>
        @endif

        @if ($this->currentSubscription)
            <flux:card>
                <flux:heading size="base" class="mb-3">{{ 'Langganan saat ini' }}</flux:heading>
                <div class="grid gap-2 sm:grid-cols-2">
                    <div>
                        <flux:text class="text-zinc-500 dark:text-zinc-400">{{ 'Paket' }}</flux:text>
                        <flux:heading size="lg">{{ $this->currentSubscription->plan?->name ?? 'Aktif' }}</flux:heading>
                    </div>
                    <div>
                        <flux:text class="text-zinc-500 dark:text-zinc-400">{{ 'Berlaku hingga' }}</flux:text>
                        <flux:heading size="lg">{{ $this->currentSubscription->ends_at->format('d F Y') }}</flux:heading>
                    </div>
                </div>
            </flux:card>
        @else
            <flux:callout variant="warning">
                {{ 'Anda tidak memiliki langganan aktif. Ajukan perpanjangan di bawah atau hubungi admin.' }}
            </flux:callout>
        @endif

        @if ($this->pendingRequest)
            <flux:callout variant="info">
                {{ 'Anda punya permintaan perpanjangan tertunda' }}: {{ $this->pendingRequest->plan?->name }}.
                {{ 'Menunggu persetujuan admin.' }}
            </flux:callout>
        @else
            <flux:card>
                <flux:heading size="base" class="mb-3">{{ 'Perpanjang langganan' }}</flux:heading>
                <flux:text class="mb-4 block">{{ 'Pilih paket. Permintaan Anda akan ditinjau admin sebelum aktivasi.' }}</flux:text>
                <form wire:submit="requestExtension" class="flex flex-wrap items-end gap-4">
                    <flux:field>
                        <flux:label>{{ 'Paket' }}</flux:label>
                        <flux:select wire:model="selected_plan_id" required>
                            <option value="">{{ 'Pilih paket...' }}</option>
                            @foreach ($this->plans as $plan)
                                <option value="{{ $plan->id }}">{{ $plan->name }} — {{ $plan->duration_months }} {{ 'bulan' }} ({{ number_format($plan->price, 0, ',', '.') }})</option>
                            @endforeach
                        </flux:select>
                        <flux:error name="selected_plan_id" />
                    </flux:field>
                    <flux:button type="submit" variant="primary">{{ 'Ajukan perpanjangan' }}</flux:button>
                </form>
                @if ($this->plans->isEmpty())
                    <flux:text class="mt-2 text-zinc-500 dark:text-zinc-400">{{ 'Tidak ada paket. Hubungi admin.' }}</flux:text>
                @endif
            </flux:card>
        @endif
    </div>
</div>
