<?php

namespace App\Domains\Subscription\Livewire\Admin;

use App\Domains\Subscription\Models\SubscriptionPlan;
use Livewire\Component;

class PlanForm extends Component
{
    public ?int $planId = null;

    public string $name = '';

    public string $duration_months = '1';

    public string $price = '0';

    public bool $is_active = true;

    public function mount(?int $planId = null): void
    {
        if ($planId !== null) {
            $plan = SubscriptionPlan::find($planId);
            if (! $plan) {
                abort(404);
            }
            $this->planId = $plan->id;
            $this->name = $plan->name;
            $this->duration_months = (string) $plan->duration_months;
            $this->price = (string) $plan->price;
            $this->is_active = $plan->is_active;
        }
    }

    public function save(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'duration_months' => ['required', 'integer', 'min:1', 'max:120'],
            'price' => ['required', 'numeric', 'min:0'],
            'is_active' => ['boolean'],
        ]);

        $data = [
            'name' => $this->name,
            'duration_months' => (int) $this->duration_months,
            'price' => (float) $this->price,
            'is_active' => $this->is_active,
        ];

        if ($this->planId !== null) {
            SubscriptionPlan::findOrFail($this->planId)->update($data);
            session()->flash('message', 'Paket berhasil diperbarui.');
        } else {
            SubscriptionPlan::create($data);
            session()->flash('message', 'Paket berhasil dibuat.');
        }
        $this->redirectRoute('admin.plans', navigate: true);
    }

    public function render()
    {
        $title = $this->planId ? 'Ubah Paket' : 'Paket Baru';

        return view('domains.subscription.livewire.admin.plan-form')
            ->layout('layouts.app', ['title' => $title]);
    }
}
