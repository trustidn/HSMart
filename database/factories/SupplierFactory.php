<?php

namespace Database\Factories;

use App\Domains\Purchasing\Models\Supplier;
use App\Domains\Tenant\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Supplier>
 */
class SupplierFactory extends Factory
{
    protected $model = Supplier::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'name' => fake()->company(),
            'contact' => fake()->optional(0.7)->name(),
            'phone' => fake()->optional(0.7)->phoneNumber(),
            'address' => fake()->optional(0.6)->address(),
        ];
    }
}
