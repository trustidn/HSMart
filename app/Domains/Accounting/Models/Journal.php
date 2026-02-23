<?php

namespace App\Domains\Accounting\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Journal extends Model
{
    use BelongsToTenant;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'tenant_id',
        'number',
        'date',
        'description',
        'reference_type',
        'reference_id',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }

    /**
     * @return HasMany<JournalItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(JournalItem::class);
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function reference(): MorphTo
    {
        return $this->morphTo();
    }
}
