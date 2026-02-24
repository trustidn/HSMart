<?php

namespace App\Domains\Subscription\Models;

use App\Domains\Tenant\Models\Tenant;
use Database\Factories\SubscriptionFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends Model
{
    use HasFactory;

    protected static function newFactory(): SubscriptionFactory
    {
        return SubscriptionFactory::new();
    }

    public const STATUS_TRIAL = 'trial';

    public const STATUS_ACTIVE = 'active';

    public const STATUS_EXPIRED = 'expired';

    public const STATUS_PENDING = 'pending';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'tenant_id',
        'subscription_plan_id',
        'started_at',
        'ends_at',
        'status',
        'price',
        'duration_days',
    ];

    /**
     * @return BelongsTo<SubscriptionPlan, $this>
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class, 'subscription_plan_id');
    }

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'ends_at' => 'datetime',
            'price' => 'decimal:2',
        ];
    }

    /**
     * Get the tenant that owns the subscription.
     *
     * @return BelongsTo<Tenant, $this>
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Scope to current subscription (trial or active, and not yet ended).
     * Uses start of today so "ends_at" on a given date is valid for the whole of that day.
     *
     * @param  Builder<Subscription>  $query
     * @return Builder<Subscription>
     */
    public function scopeCurrent(Builder $query): Builder
    {
        return $query->whereIn('status', [self::STATUS_TRIAL, self::STATUS_ACTIVE])
            ->where('ends_at', '>=', now()->startOfDay());
    }
}
