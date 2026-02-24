<?php

namespace Database\Factories;

use App\Domains\Subscription\Models\SubscriptionPlan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SubscriptionPlan>
 */
class SubscriptionPlanFactory extends Factory
{
    protected $model = SubscriptionPlan::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(['1 Bulan', '3 Bulan', '6 Bulan', '1 Tahun']),
            'duration_months' => fake()->randomElement([1, 3, 6, 12]),
            'price' => fake()->randomElement([50000, 120000, 200000, 350000]),
            'is_active' => true,
        ];
    }
}
