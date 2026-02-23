<div>
    <flux:heading size="lg">{{ __('Subscriptions') }}</flux:heading>

    @if (session('subscription_message'))
        <flux:callout variant="success" class="mt-2">{{ session('subscription_message') }}</flux:callout>
    @endif

    <div class="mt-4 space-y-6">
        <flux:card>
            <flux:heading size="base" class="mb-3">{{ __('Add subscription') }}</flux:heading>
            <form wire:submit="addSubscription" class="flex flex-wrap items-end gap-4">
                <flux:field>
                    <flux:label>{{ __('Duration (days)') }}</flux:label>
                    <flux:input type="number" wire:model="duration_days" min="1" max="3650" required />
                    <flux:error name="duration_days" />
                </flux:field>
                <flux:field>
                    <flux:label>{{ __('Status') }}</flux:label>
                    <flux:select wire:model="status">
                        <option value="{{ \App\Domains\Subscription\Models\Subscription::STATUS_TRIAL }}">{{ __('Trial') }}</option>
                        <option value="{{ \App\Domains\Subscription\Models\Subscription::STATUS_ACTIVE }}">{{ __('Active') }}</option>
                    </flux:select>
                </flux:field>
                <flux:button type="submit" variant="primary">{{ __('Add') }}</flux:button>
            </form>
        </flux:card>

        <flux:table>
            <flux:table.columns>
                <flux:table.row>
                    <flux:table.cell variant="strong">{{ __('Started') }}</flux:table.cell>
                    <flux:table.cell variant="strong">{{ __('Ends at') }}</flux:table.cell>
                    <flux:table.cell variant="strong">{{ __('Status') }}</flux:table.cell>
                    <flux:table.cell variant="strong">{{ __('Duration') }}</flux:table.cell>
                    <flux:table.cell variant="strong" class="w-0"></flux:table.cell>
                </flux:table.row>
            </flux:table.columns>
            <flux:table.rows>
                @forelse ($this->subscriptions as $sub)
                    <flux:table.row :key="$sub->id">
                        <flux:table.cell>{{ $sub->started_at->format('d/m/Y') }}</flux:table.cell>
                        <flux:table.cell>{{ $sub->ends_at->format('d/m/Y') }}</flux:table.cell>
                        <flux:table.cell>
                            <flux:badge :color="$sub->ends_at >= now() ? 'green' : 'red'">
                                {{ ucfirst($sub->status) }}
                            </flux:badge>
                        </flux:table.cell>
                        <flux:table.cell>{{ $sub->duration_days }} {{ __('days') }}</flux:table.cell>
                        <flux:table.cell>
                            @if($extendSubscriptionId === $sub->id)
                                <form wire:submit="extendSubscription({{ $sub->id }})" class="flex items-center gap-2">
                                    <flux:input type="number" wire:model="extendDays" min="1" class="w-20" />
                                    <flux:error name="extendDays" class="text-xs" />
                                    <flux:button type="submit" size="sm">{{ __('Add days') }}</flux:button>
                                    <flux:button type="button" size="sm" variant="ghost" wire:click="cancelExtend">{{ __('Cancel') }}</flux:button>
                                </form>
                            @else
                                <flux:button size="sm" variant="ghost" wire:click="openExtendModal({{ $sub->id }})">
                                    {{ __('Extend') }}
                                </flux:button>
                            @endif
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="5" class="text-center text-zinc-500 dark:text-zinc-400">
                            {{ __('No subscriptions.') }}
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </div>

</div>
