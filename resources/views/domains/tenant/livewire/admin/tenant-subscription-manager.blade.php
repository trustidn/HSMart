<div>
    <flux:heading size="lg">{{ __('Subscriptions') }}</flux:heading>

    @if (session('subscription_message'))
        <flux:callout variant="success" class="mt-2">{{ session('subscription_message') }}</flux:callout>
    @endif

    <div class="mt-4 space-y-6">
        <flux:card>
            <flux:heading size="base" class="mb-3">{{ __('Add subscription (choose plan)') }}</flux:heading>
            <form wire:submit="addSubscription" class="flex flex-wrap items-end gap-4">
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
                <flux:field>
                    <flux:label>{{ __('Status') }}</flux:label>
                    <flux:select wire:model="status">
                        <option value="{{ \App\Domains\Subscription\Models\Subscription::STATUS_TRIAL }}">{{ __('Trial') }}</option>
                        <option value="{{ \App\Domains\Subscription\Models\Subscription::STATUS_ACTIVE }}">{{ __('Active') }}</option>
                    </flux:select>
                </flux:field>
                <flux:button type="submit" variant="primary">{{ __('Add') }}</flux:button>
            </form>
            @if ($this->plans->isEmpty())
                <flux:text class="mt-2 text-amber-600 dark:text-amber-400">{{ __('No active plans. Create plans in Subscription Plans first.') }}</flux:text>
            @endif
        </flux:card>

        @php
            $pending = $this->subscriptions->where('status', \App\Domains\Subscription\Models\Subscription::STATUS_PENDING);
        @endphp
        @if ($pending->isNotEmpty())
            <flux:card>
                <flux:heading size="base" class="mb-3">{{ __('Pending requests (tenant requested extension)') }}</flux:heading>
                <flux:table>
                    <flux:table.columns>
                        <flux:table.row>
                            <flux:table.cell variant="strong">{{ __('Plan') }}</flux:table.cell>
                            <flux:table.cell variant="strong">{{ __('Requested') }}</flux:table.cell>
                            <flux:table.cell variant="strong" class="w-0"></flux:table.cell>
                        </flux:table.row>
                    </flux:table.columns>
                    <flux:table.rows>
                        @foreach ($pending as $sub)
                            <flux:table.row :key="'pending-'.$sub->id">
                                <flux:table.cell>{{ $sub->plan?->name ?? __('Plan') }}</flux:table.cell>
                                <flux:table.cell>{{ $sub->created_at->format('d/m/Y H:i') }}</flux:table.cell>
                                <flux:table.cell>
                                    <flux:button size="sm" variant="primary" wire:click="approveSubscription({{ $sub->id }})">
                                        {{ __('Approve') }}
                                    </flux:button>
                                </flux:table.cell>
                            </flux:table.row>
                        @endforeach
                    </flux:table.rows>
                </flux:table>
            </flux:card>
        @endif

        <flux:table>
            <flux:table.columns>
                <flux:table.row>
                    <flux:table.cell variant="strong">{{ __('Plan') }}</flux:table.cell>
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
                        <flux:table.cell>{{ $sub->plan?->name ?? '—' }}</flux:table.cell>
                        <flux:table.cell>{{ $sub->started_at?->format('d/m/Y') ?? '—' }}</flux:table.cell>
                        <flux:table.cell>{{ $sub->ends_at?->format('d/m/Y') ?? '—' }}</flux:table.cell>
                        <flux:table.cell>
                            <flux:badge :color="$sub->status === 'pending' ? 'amber' : ($sub->ends_at && $sub->ends_at >= now() ? 'green' : 'red')">
                                {{ ucfirst($sub->status) }}
                            </flux:badge>
                        </flux:table.cell>
                        <flux:table.cell>{{ $sub->duration_days ?? '—' }} {{ $sub->duration_days ? __('days') : '' }}</flux:table.cell>
                        <flux:table.cell>
                            @if($sub->status !== \App\Domains\Subscription\Models\Subscription::STATUS_PENDING)
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
                            @endif
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="6" class="text-center text-zinc-500 dark:text-zinc-400">
                            {{ __('No subscriptions.') }}
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </div>

</div>
