<?php

namespace App\Domains\Accounting\Services;

use App\Domains\Accounting\Models\Account;
use App\Domains\Accounting\Models\Journal;
use App\Domains\Accounting\Models\JournalItem;
use App\Domains\POS\Models\Sale;
use App\Domains\Tenant\Models\Tenant;
use Illuminate\Support\Facades\DB;

class JournalService
{
    public const CODE_KAS = '1100';

    public const CODE_PENJUALAN = '4100';

    public const CODE_PERSEDIAAN = '1200';

    public const CODE_HUTANG = '2100';

    /**
     * Create a journal with double-entry items. Sum of debit must equal sum of credit.
     *
     * @param  array<int, array{account_id: int, debit: float, credit: float, description?: string}>  $entries
     */
    public function createJournal(
        Tenant $tenant,
        \DateTimeInterface|string $date,
        string $description,
        ?string $referenceType = null,
        ?int $referenceId = null,
        array $entries = []
    ): Journal {
        $debitTotal = array_sum(array_column($entries, 'debit'));
        $creditTotal = array_sum(array_column($entries, 'credit'));
        if (abs($debitTotal - $creditTotal) > 0.01) {
            throw new \InvalidArgumentException('Journal entries must balance: debit and credit totals must be equal.');
        }

        return DB::transaction(function () use ($tenant, $date, $description, $referenceType, $referenceId, $entries) {
            $journal = Journal::withoutGlobalScopes()->create([
                'tenant_id' => $tenant->id,
                'number' => $this->generateJournalNumber($tenant),
                'date' => is_string($date) ? $date : $date->format('Y-m-d'),
                'description' => $description,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
            ]);

            foreach ($entries as $entry) {
                JournalItem::create([
                    'journal_id' => $journal->id,
                    'account_id' => $entry['account_id'],
                    'debit' => round((float) ($entry['debit'] ?? 0), 2),
                    'credit' => round((float) ($entry['credit'] ?? 0), 2),
                    'description' => $entry['description'] ?? null,
                ]);
            }

            return $journal;
        });
    }

    /**
     * Record journal for a completed sale: Debit Kas, Credit Penjualan.
     */
    public function recordSaleJournal(Sale $sale): Journal
    {
        $tenant = $sale->tenant;
        $this->ensureDefaultAccounts($tenant);

        $kas = Account::withoutGlobalScopes()->where('tenant_id', $tenant->id)->where('code', self::CODE_KAS)->firstOrFail();
        $penjualan = Account::withoutGlobalScopes()->where('tenant_id', $tenant->id)->where('code', self::CODE_PENJUALAN)->firstOrFail();

        $amount = (float) $sale->total_amount;
        if ($amount <= 0) {
            return $this->createJournal(
                $tenant,
                $sale->sale_date,
                'Penjualan #'.$sale->sale_number.' (nilai nol)',
                $sale->getMorphClass(),
                $sale->id,
                []
            );
        }

        return $this->createJournal(
            $tenant,
            $sale->sale_date,
            'Penjualan #'.$sale->sale_number,
            $sale->getMorphClass(),
            $sale->id,
            [
                ['account_id' => $kas->id, 'debit' => $amount, 'credit' => 0, 'description' => 'Penjualan #'.$sale->sale_number],
                ['account_id' => $penjualan->id, 'debit' => 0, 'credit' => $amount, 'description' => 'Penjualan #'.$sale->sale_number],
            ]
        );
    }

    /**
     * Create default chart of accounts for tenant if none exist.
     */
    public function ensureDefaultAccounts(Tenant $tenant): void
    {
        $exists = Account::withoutGlobalScopes()->where('tenant_id', $tenant->id)->exists();
        if ($exists) {
            return;
        }

        $defaults = [
            ['code' => self::CODE_KAS, 'name' => 'Kas', 'type' => Account::TYPE_ASSET],
            ['code' => self::CODE_PERSEDIAAN, 'name' => 'Persediaan Barang', 'type' => Account::TYPE_ASSET],
            ['code' => self::CODE_HUTANG, 'name' => 'Hutang Usaha', 'type' => Account::TYPE_LIABILITY],
            ['code' => self::CODE_PENJUALAN, 'name' => 'Penjualan', 'type' => Account::TYPE_REVENUE],
        ];

        foreach ($defaults as $row) {
            Account::withoutGlobalScopes()->create([
                'tenant_id' => $tenant->id,
                'code' => $row['code'],
                'name' => $row['name'],
                'type' => $row['type'],
                'is_active' => true,
            ]);
        }
    }

    private function generateJournalNumber(Tenant $tenant): string
    {
        $count = Journal::withoutGlobalScopes()->where('tenant_id', $tenant->id)->count();

        return 'J-'.str_pad((string) ($count + 1), 6, '0', STR_PAD_LEFT);
    }
}
