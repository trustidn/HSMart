<?php

namespace App\Livewire;

use App\Domains\Subscription\Models\Subscription;
use App\Domains\Tenant\Models\Tenant;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        $layout = ['title' => __('Dashboard')];

        if (tenant() !== null) {
            return view('livewire.dashboard-tenant')->layout('layouts.app', $layout);
        }

        return view('livewire.dashboard-admin')->layout('layouts.app', $layout);
    }

    /**
     * Stats for admin dashboard (only meaningful when tenant() is null).
     *
     * @return array{total_tenants: int, active_subscriptions: int, trial: int, expired: int}
     */
    #[Computed]
    public function adminStats(): array
    {
        if (tenant() !== null) {
            return ['total_tenants' => 0, 'active_subscriptions' => 0, 'trial' => 0, 'expired' => 0];
        }

        $totalTenants = Tenant::count();
        $activeSubscriptions = Subscription::query()->current()->count();
        $trial = Subscription::where('status', Subscription::STATUS_TRIAL)->where('ends_at', '>=', now())->count();
        $expired = Subscription::where('ends_at', '<', now())
            ->orWhere('status', Subscription::STATUS_EXPIRED)
            ->count();

        return [
            'total_tenants' => $totalTenants,
            'active_subscriptions' => $activeSubscriptions,
            'trial' => $trial,
            'expired' => $expired,
        ];
    }

    /**
     * Tenants with current subscription info for admin dashboard.
     */
    #[Computed]
    public function tenantsWithSubscription()
    {
        if (tenant() !== null) {
            return collect();
        }

        return Tenant::query()
            ->withCount('users')
            ->with(['subscriptions' => fn ($q) => $q->orderByDesc('ends_at')->limit(1)])
            ->orderBy('name')
            ->limit(15)
            ->get();
    }
}
