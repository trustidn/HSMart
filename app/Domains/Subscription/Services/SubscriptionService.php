<?php

namespace App\Domains\Subscription\Services;

use App\Domains\Subscription\Models\Subscription;
use App\Domains\Tenant\Models\Tenant;
use Carbon\CarbonImmutable;

class SubscriptionService
{
    public const TRIAL_DAYS = 7;

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
}
