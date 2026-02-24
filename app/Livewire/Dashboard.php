<?php

namespace App\Livewire;

use App\Domains\POS\Models\Sale;
use App\Domains\Product\Models\Product;
use App\Domains\Subscription\Models\Subscription;
use App\Domains\Tenant\Models\Tenant;
use Carbon\Carbon;
use Illuminate\Support\Str;
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
     * Greeting for tenant dashboard (e.g. "Good morning, John").
     */
    #[Computed]
    public function greeting(): string
    {
        if (tenant() === null) {
            return __('Dashboard');
        }

        $user = auth()->user();
        $name = $user ? (Str::before($user->name, ' ') ?: $user->name) : __('User');
        $hour = $this->now()->hour;

        if ($hour >= 5 && $hour < 12) {
            return __('Good morning, :name', ['name' => $name]);
        }
        if ($hour >= 12 && $hour < 15) {
            return __('Good afternoon, :name', ['name' => $name]);
        }
        if ($hour >= 15 && $hour < 19) {
            return __('Good evening, :name', ['name' => $name]);
        }

        return __('Good night, :name', ['name' => $name]);
    }

    /**
     * Current date in tenant timezone, formatted for display.
     */
    #[Computed]
    public function currentDate(): string
    {
        return $this->now()->isoFormat('dddd, D MMMM Y');
    }

    /**
     * Stats for tenant dashboard: products, low stock, today's sales.
     *
     * @return array{total_products: int, low_stock_count: int, today_sales_count: int, today_revenue: float}
     */
    #[Computed]
    public function tenantStats(): array
    {
        if (tenant() === null) {
            return [
                'total_products' => 0,
                'low_stock_count' => 0,
                'today_sales_count' => 0,
                'today_revenue' => 0.0,
            ];
        }

        $today = $this->now()->toDateString();

        return [
            'total_products' => Product::count(),
            'low_stock_count' => Product::where('minimum_stock', '>', 0)
                ->whereColumn('stock', '<=', 'minimum_stock')
                ->count(),
            'today_sales_count' => Sale::whereDate('sale_date', $today)->count(),
            'today_revenue' => (float) Sale::whereDate('sale_date', $today)->sum('total_amount'),
        ];
    }

    /**
     * Recent sales for tenant dashboard.
     */
    #[Computed]
    public function recentSales()
    {
        if (tenant() === null) {
            return collect();
        }

        return Sale::query()
            ->orderByDesc('sale_date')
            ->orderByDesc('id')
            ->limit(10)
            ->get();
    }

    private function now(): Carbon
    {
        $t = tenant();
        $tz = $t?->setting?->timezone ?? config('app.timezone');

        return Carbon::now($tz);
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
