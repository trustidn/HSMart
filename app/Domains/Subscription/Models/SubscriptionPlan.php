<?php

namespace App\Domains\Subscription\Models;

use Database\Factories\SubscriptionPlanFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubscriptionPlan extends Model
{
    use HasFactory;

    protected static function newFactory(): SubscriptionPlanFactory
    {
        return SubscriptionPlanFactory::new();
    }

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'duration_months',
        'price',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'duration_months' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return Builder<Subscription>
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class, 'subscription_plan_id');
    }

    public function getDurationDaysAttribute(): int
    {
        return (int) $this->duration_months * 30;
    }
}
