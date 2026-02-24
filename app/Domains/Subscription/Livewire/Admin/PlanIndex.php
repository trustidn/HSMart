<?php

namespace App\Domains\Subscription\Livewire\Admin;

use App\Domains\Subscription\Models\SubscriptionPlan;
use Livewire\Component;
use Livewire\WithPagination;

class PlanIndex extends Component
{
    use WithPagination;

    public function getPlansProperty()
    {
        return SubscriptionPlan::query()
            ->orderBy('duration_months')
            ->paginate(10);
    }

    public function render()
    {
        return view('domains.subscription.livewire.admin.plan-index')
            ->layout('layouts.app', ['title' => __('Subscription Plans')]);
    }
}
