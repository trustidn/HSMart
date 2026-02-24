<?php

namespace App\Domains\Tenant\Livewire\Admin;

use App\Domains\Subscription\Models\Subscription;
use App\Domains\Subscription\Services\SubscriptionService;
use App\Domains\Tenant\Models\Tenant;
use Livewire\Component;

class TenantSubscriptionManager extends Component
{
    public int $tenantId;

    /** @var int|null Selected plan ID when adding subscription */
    public ?int $selected_plan_id = null;

    public string $status = Subscription::STATUS_ACTIVE;

    public int $extendDays = 30;

    public ?int $extendSubscriptionId = null;

    public function mount(int $tenantId): void
    {
        $tenant = Tenant::find($tenantId);
        if (! $tenant) {
            abort(404);
        }
        $this->tenantId = $tenantId;
    }

    public function getPlansProperty()
    {
        return app(SubscriptionService::class)->getActivePlans();
    }

    public function addSubscription(): void
    {
        $this->validate([
            'selected_plan_id' => ['required', 'exists:subscription_plans,id'],
            'status' => ['required', 'in:'.Subscription::STATUS_TRIAL.','.Subscription::STATUS_ACTIVE],
        ]);
        $tenant = Tenant::findOrFail($this->tenantId);
        app(SubscriptionService::class)->addSubscriptionByPlan($tenant, (int) $this->selected_plan_id, [
            'status' => $this->status,
        ]);
        session()->flash('subscription_message', __('Subscription added.'));
        $this->selected_plan_id = null;
        $this->redirectRoute('admin.tenants.edit', ['tenantId' => $this->tenantId], navigate: true);
    }

    public function approveSubscription(int $subscriptionId): void
    {
        $subscription = Subscription::findOrFail($subscriptionId);
        if ($subscription->tenant_id !== $this->tenantId) {
            abort(403);
        }
        app(SubscriptionService::class)->approveSubscription($subscription);
        session()->flash('subscription_message', __('Subscription approved and activated.'));
        $this->redirectRoute('admin.tenants.edit', ['tenantId' => $this->tenantId], navigate: true);
    }

    public function extendSubscription(int $subscriptionId): void
    {
        $this->validate([
            'extendDays' => ['required', 'integer', 'min:1', 'max:3650'],
        ]);
        $subscription = Subscription::findOrFail($subscriptionId);
        if ($subscription->tenant_id !== $this->tenantId) {
            abort(403);
        }
        app(SubscriptionService::class)->extendSubscription($subscription, $this->extendDays);
        session()->flash('subscription_message', __('Subscription extended.'));
        $this->extendSubscriptionId = null;
        $this->extendDays = 30;
        $this->redirectRoute('admin.tenants.edit', ['tenantId' => $this->tenantId], navigate: true);
    }

    public function openExtendModal(int $subscriptionId): void
    {
        $this->extendSubscriptionId = $subscriptionId;
        $this->extendDays = 30;
    }

    public function cancelExtend(): void
    {
        $this->extendSubscriptionId = null;
    }

    public function getTenantProperty(): ?Tenant
    {
        return Tenant::find($this->tenantId);
    }

    public function getSubscriptionsProperty()
    {
        return Subscription::where('tenant_id', $this->tenantId)
            ->with('plan')
            ->orderByDesc('created_at')
            ->get();
    }

    public function render()
    {
        return view('domains.tenant.livewire.admin.tenant-subscription-manager');
    }
}
