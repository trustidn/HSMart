<div>
    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl">
        <flux:heading size="xl">{{ __('Subscription') }}</flux:heading>

        @if (session('subscription_message'))
            <flux:callout variant="success">{{ session('subscription_message') }}</flux:callout>
        @endif

        @if ($this->currentSubscription)
            <flux:card>
                <flux:heading size="base" class="mb-3">{{ __('Current subscription') }}</flux:heading>
                <div class="grid gap-2 sm:grid-cols-2">
                    <div>
                        <flux:text class="text-zinc-500 dark:text-zinc-400">{{ __('Plan') }}</flux:text>
                        <flux:heading size="lg">{{ $this->currentSubscription->plan?->name ?? __('Active') }}</flux:heading>
                    </div>
                    <div>
                        <flux:text class="text-zinc-500 dark:text-zinc-400">{{ __('Valid until') }}</flux:text>
                        <flux:heading size="lg">{{ $this->currentSubscription->ends_at->format('d F Y') }}</flux:heading>
                    </div>
                </div>
            </flux:card>
        @else
            <flux:callout variant="warning">
                {{ __('You do not have an active subscription. Request an extension below or contact admin.') }}
            </flux:callout>
        @endif

        @if ($this->pendingRequest)
            <flux:callout variant="info">
                {{ __('You have a pending extension request') }}: {{ $this->pendingRequest->plan?->name }}.
                {{ __('Waiting for admin approval.') }}
            </flux:callout>
        @else
            <flux:card>
                <flux:heading size="base" class="mb-3">{{ __('Extend subscription') }}</flux:heading>
                <flux:text class="mb-4 block">{{ __('Choose a plan. Your request will be reviewed by admin before activation.') }}</flux:text>
                <form wire:submit="requestExtension" class="flex flex-wrap items-end gap-4">
                    <flux:field>
                        <flux:label>{{ __('Plan') }}</flux:label>
                        <flux:select wire:model="selected_plan_id" required>
                            <option value="">{{ __('Select plan...') }}</option>
                            @foreach ($this->plans as $plan)
                                <option value="{{ $plan->id }}">{{ $plan->name }} — {{ $plan->duration_months }} {{ __('months') }} ({{ number_format($plan->price, 0, ',', '.') }})</option>
                            @endforeach
                        </flux:select>
                        <flux:error name="selected_plan_id" />
                    </flux:field>
                    <flux:button type="submit" variant="primary">{{ __('Request extension') }}</flux:button>
                </form>
                @if ($this->plans->isEmpty())
                    <flux:text class="mt-2 text-zinc-500 dark:text-zinc-400">{{ __('No plans available. Contact admin.') }}</flux:text>
                @endif
            </flux:card>
        @endif
    </div>
</div>
