<?php

use App\Domains\Accounting\Models\Account;
use App\Domains\Accounting\Models\Journal;
use App\Domains\Accounting\Services\JournalService;
use App\Domains\Tenant\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->tenant = Tenant::factory()->create();
    app()->instance('tenant', $this->tenant);
    app(JournalService::class)->ensureDefaultAccounts($this->tenant);
});

test('ensureDefaultAccounts creates Kas, Persediaan, Hutang, Penjualan', function () {
    $accounts = Account::withoutGlobalScopes()->where('tenant_id', $this->tenant->id)->orderBy('code')->get();

    expect($accounts)->toHaveCount(4)
        ->and($accounts->pluck('code')->all())->toBe(['1100', '1200', '2100', '4100']);
});

test('createJournal creates journal with balanced entries', function () {
    $kas = Account::withoutGlobalScopes()->where('tenant_id', $this->tenant->id)->where('code', '1100')->first();
    $penjualan = Account::withoutGlobalScopes()->where('tenant_id', $this->tenant->id)->where('code', '4100')->first();

    $journal = app(JournalService::class)->createJournal(
        $this->tenant,
        now()->toDateString(),
        'Test journal',
        null,
        null,
        [
            ['account_id' => $kas->id, 'debit' => 10000, 'credit' => 0],
            ['account_id' => $penjualan->id, 'debit' => 0, 'credit' => 10000],
        ]
    );

    expect($journal)->toBeInstanceOf(Journal::class)
        ->and($journal->items)->toHaveCount(2)
        ->and($journal->items->sum('debit'))->toBe(10000.0)
        ->and($journal->items->sum('credit'))->toBe(10000.0);
});

test('createJournal throws when entries do not balance', function () {
    $kas = Account::withoutGlobalScopes()->where('tenant_id', $this->tenant->id)->where('code', '1100')->first();

    app(JournalService::class)->createJournal(
        $this->tenant,
        now()->toDateString(),
        'Unbalanced',
        null,
        null,
        [
            ['account_id' => $kas->id, 'debit' => 10000, 'credit' => 0],
        ]
    );
})->throws(\InvalidArgumentException::class);
