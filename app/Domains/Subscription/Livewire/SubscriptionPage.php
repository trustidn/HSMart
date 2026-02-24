<?php

namespace App\Domains\Subscription\Livewire;

use App\Domains\Subscription\Services\SubscriptionService;
use App\Domains\Tenant\Models\Tenant;
use Livewire\Component;

class SubscriptionPage extends Component
{
    public ?int $selected_plan_id = null;

    public function getTenantProperty(): ?Tenant
    {
        $user = auth()->user();
        if ($user?->tenant_id === null) {
            return null;
        }

        return Tenant::find($user->tenant_id);
    }

    public function getCurrentSubscriptionProperty()
    {
        $tenant = $this->tenant;
        if (! $tenant) {
            return null;
        }

        return app(SubscriptionService::class)->getCurrentSubscription($tenant);
    }

    public function getPlansProperty()
    {
        return app(SubscriptionService::class)->getActivePlans();
    }

    public function getPendingRequestProperty()
    {
        $tenant = $this->tenant;
        if (! $tenant) {
            return null;
        }

        return $tenant->subscriptions()
            ->where('status', \App\Domains\Subscription\Models\Subscription::STATUS_PENDING)
            ->with('plan')
            ->latest()
            ->first();
    }

    public function requestExtension(): void
    {
        $this->validate([
            'selected_plan_id' => ['required', 'exists:subscription_plans,id'],
        ]);
        $tenant = $this->tenant;
        if (! $tenant) {
            abort(403);
        }
        app(SubscriptionService::class)->requestExtension($tenant, (int) $this->selected_plan_id);
        session()->flash('subscription_message', 'Permintaan perpanjangan dikirim. Menunggu persetujuan admin.');
        $this->selected_plan_id = null;
    }

    public function render()
    {
        return view('domains.subscription.livewire.subscription-page')
            ->layout('layouts.app', ['title' => 'Langganan']);
    }
}
