<?php

namespace App\Domains\Accounting\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Account extends Model
{
    use BelongsToTenant;

    public const TYPE_ASSET = 'asset';

    public const TYPE_LIABILITY = 'liability';

    public const TYPE_EQUITY = 'equity';

    public const TYPE_REVENUE = 'revenue';

    public const TYPE_EXPENSE = 'expense';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'tenant_id',
        'code',
        'name',
        'type',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return HasMany<JournalItem, $this>
     */
    public function journalItems(): HasMany
    {
        return $this->hasMany(JournalItem::class);
    }
}
