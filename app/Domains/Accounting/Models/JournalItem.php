<?php

namespace App\Domains\Accounting\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JournalItem extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'journal_id',
        'account_id',
        'debit',
        'credit',
        'description',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'debit' => 'decimal:2',
            'credit' => 'decimal:2',
        ];
    }

    /**
     * @return BelongsTo<Journal, $this>
     */
    public function journal(): BelongsTo
    {
        return $this->belongsTo(Journal::class);
    }

    /**
     * @return BelongsTo<Account, $this>
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}
