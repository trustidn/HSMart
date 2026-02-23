<?php

namespace Database\Factories;

use App\Domains\Subscription\Models\Subscription;
use App\Domains\Tenant\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Subscription>
 */
class SubscriptionFactory extends Factory
{
    protected $model = Subscription::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startedAt = now();
        $endsAt = $startedAt->copy()->addDays(7);

        return [
            'tenant_id' => Tenant::factory(),
            'started_at' => $startedAt,
            'ends_at' => $endsAt,
            'status' => Subscription::STATUS_ACTIVE,
            'price' => null,
            'duration_days' => 7,
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Subscription::STATUS_ACTIVE,
            'ends_at' => now()->addDays(7),
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Subscription::STATUS_EXPIRED,
            'ends_at' => now()->subDay(),
        ]);
    }
}
