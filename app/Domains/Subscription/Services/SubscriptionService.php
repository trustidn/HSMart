<?php

namespace App\Domains\Subscription\Services;

use App\Domains\Subscription\Models\Subscription;
use App\Domains\Subscription\Models\SubscriptionPlan;
use App\Domains\Tenant\Models\Tenant;
use Carbon\CarbonImmutable;

class SubscriptionService
{
    public const TRIAL_DAYS = 7;

    /**
     * Get active subscription plans (for tenant to choose when extending, and admin when assigning).
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, SubscriptionPlan>
     */
    public function getActivePlans(): \Illuminate\Database\Eloquent\Collection
    {
        return SubscriptionPlan::where('is_active', true)
            ->orderBy('duration_months')
            ->get();
    }

    /**
     * Add subscription for tenant using a plan (superadmin). Immediately active.
     *
     * @param  array{status?: string}  $options
     */
    public function addSubscriptionByPlan(Tenant $tenant, int $planId, array $options = []): Subscription
    {
        $plan = SubscriptionPlan::findOrFail($planId);
        if (! $plan->is_active) {
            throw new \InvalidArgumentException(__('Selected plan is not active.'));
        }
        $durationDays = $plan->duration_months * 30;
        $startedAt = CarbonImmutable::now();
        $endsAt = $startedAt->addDays($durationDays);
        $status = $options['status'] ?? Subscription::STATUS_ACTIVE;

        return Subscription::create([
            'tenant_id' => $tenant->id,
            'subscription_plan_id' => $plan->id,
            'started_at' => $startedAt,
            'ends_at' => $endsAt,
            'status' => $status,
            'price' => $plan->price,
            'duration_days' => $durationDays,
        ]);
    }

    /**
     * Tenant requests to extend subscription with a plan. Creates pending subscription until admin approves.
     */
    public function requestExtension(Tenant $tenant, int $planId): Subscription
    {
        $plan = SubscriptionPlan::findOrFail($planId);
        if (! $plan->is_active) {
            throw new \InvalidArgumentException(__('Selected plan is not active.'));
        }
        $durationDays = $plan->duration_months * 30;
        $requestedAt = CarbonImmutable::now();
        $tentativeEndsAt = $requestedAt->addDays($durationDays);

        return Subscription::create([
            'tenant_id' => $tenant->id,
            'subscription_plan_id' => $plan->id,
            'started_at' => $requestedAt,
            'ends_at' => $tentativeEndsAt,
            'status' => Subscription::STATUS_PENDING,
            'price' => $plan->price,
            'duration_days' => $durationDays,
        ]);
    }

    /**
     * Admin approves a pending subscription: sets started_at/ends_at from now and status to active.
     */
    public function approveSubscription(Subscription $subscription): Subscription
    {
        if ($subscription->status !== Subscription::STATUS_PENDING) {
            throw new \DomainException(__('Only pending subscriptions can be approved.'));
        }
        $durationDays = (int) $subscription->duration_days;
        $startedAt = CarbonImmutable::now();
        $endsAt = $startedAt->addDays($durationDays);

        $subscription->update([
            'started_at' => $startedAt,
            'ends_at' => $endsAt,
            'status' => Subscription::STATUS_ACTIVE,
        ]);

        return $subscription->fresh();
    }

    /**
     * Start a 7-day trial for the tenant.
     */
    public function startTrial(Tenant $tenant): Subscription
    {
        $startedAt = CarbonImmutable::now();
        $endsAt = $startedAt->addDays(self::TRIAL_DAYS);

        return Subscription::create([
            'tenant_id' => $tenant->id,
            'started_at' => $startedAt,
            'ends_at' => $endsAt,
            'status' => Subscription::STATUS_TRIAL,
            'price' => null,
            'duration_days' => self::TRIAL_DAYS,
        ]);
    }

    /**
     * Whether the tenant has an active subscription (can use the app fully).
     */
    public function isActive(?Tenant $tenant): bool
    {
        if (! $tenant) {
            return false;
        }

        $current = $tenant->subscriptions()
            ->current()
            ->first();

        return $current !== null;
    }

    /**
     * Whether the tenant can create a new sale (subscription not expired).
     */
    public function canCreateSale(?Tenant $tenant): bool
    {
        return $this->isActive($tenant);
    }

    /**
     * Whether the tenant can create a new purchase (subscription not expired).
     */
    public function canCreatePurchase(?Tenant $tenant): bool
    {
        return $this->isActive($tenant);
    }

    /**
     * Get the current active subscription for the tenant, if any.
     */
    public function getCurrentSubscription(?Tenant $tenant): ?Subscription
    {
        if (! $tenant) {
            return null;
        }

        return $tenant->subscriptions()->current()->first();
    }

    /**
     * Add a new subscription for tenant (superadmin). Started at now, ends at now + duration_days.
     *
     * @param  array{status?: string, duration_days: int, price?: float|null}  $options
     */
    public function addSubscription(Tenant $tenant, int $durationDays, array $options = []): Subscription
    {
        $startedAt = CarbonImmutable::now();
        $endsAt = $startedAt->addDays($durationDays);
        $status = $options['status'] ?? Subscription::STATUS_ACTIVE;
        $price = $options['price'] ?? null;

        return Subscription::create([
            'tenant_id' => $tenant->id,
            'started_at' => $startedAt,
            'ends_at' => $endsAt,
            'status' => $status,
            'price' => $price,
            'duration_days' => $durationDays,
        ]);
    }

    /**
     * Extend subscription by adding days to ends_at (superadmin).
     * If status was expired, set to active so it counts as current.
     */
    public function extendSubscription(Subscription $subscription, int $days): Subscription
    {
        $endsAt = $subscription->ends_at->addDays($days);
        $updates = ['ends_at' => $endsAt];
        if ($subscription->status === Subscription::STATUS_EXPIRED) {
            $updates['status'] = Subscription::STATUS_ACTIVE;
        }
        $subscription->update($updates);

        return $subscription->fresh();
    }
}
