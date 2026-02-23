<?php

use App\Domains\POS\Models\Sale;
use App\Domains\POS\Services\SaleService;
use App\Domains\Product\Models\Product;
use App\Domains\Subscription\Models\Subscription;
use App\Domains\Tenant\Models\Tenant;
use Illuminate\Support\Facades\Event;

beforeEach(function () {
    $tenant = Tenant::factory()->create();
    $tenant->setting()->create([
        'store_name' => $tenant->name,
        'currency' => 'IDR',
        'timezone' => 'Asia/Jakarta',
    ]);
    Subscription::factory()->active()->create(['tenant_id' => $tenant->id]);
    app()->instance('tenant', $tenant);
    $this->tenant = $tenant;
});

test('SaleService creates sale with items and payment and dispatches SaleCompleted', function () {
    Event::fake([\App\Domains\POS\Events\SaleCompleted::class]);
    $product = Product::factory()->create(['tenant_id' => $this->tenant->id, 'stock' => 10, 'sell_price' => 10000]);

    $service = app(SaleService::class);
    $sale = $service->createSale(
        customerName: 'Customer A',
        items: [['product_id' => $product->id, 'qty' => 2, 'unit_price' => 10000]],
        amount: 20000,
        paymentMethod: 'cash'
    );

    expect($sale)->toBeInstanceOf(Sale::class)
        ->and($sale->sale_number)->toStartWith('INV-')
        ->and((float) $sale->total_amount)->toBe(20000.0)
        ->and($sale->items)->toHaveCount(1)
        ->and($sale->payments)->toHaveCount(1);
    Event::assertDispatched(\App\Domains\POS\Events\SaleCompleted::class);
});

test('SaleService throws when stock insufficient', function () {
    $product = Product::factory()->create(['tenant_id' => $this->tenant->id, 'stock' => 2, 'sell_price' => 10000]);
    $service = app(SaleService::class);

    $service->createSale(
        customerName: null,
        items: [['product_id' => $product->id, 'qty' => 5, 'unit_price' => 10000]],
        amount: 50000,
        paymentMethod: 'cash'
    );
})->throws(\DomainException::class);

test('DeductSaleStock listener decreases product stock', function () {
    $product = Product::factory()->create(['tenant_id' => $this->tenant->id, 'stock' => 10]);
    $sale = Sale::withoutGlobalScopes()->create([
        'tenant_id' => $this->tenant->id,
        'sale_number' => 'INV-000001',
        'sale_date' => now(),
        'total_amount' => 15000,
        'status' => 'completed',
    ]);
    $sale->items()->create([
        'product_id' => $product->id,
        'qty' => 3,
        'unit_price' => 5000,
        'subtotal' => 15000,
    ]);

    event(new \App\Domains\POS\Events\SaleCompleted($sale));

    expect($product->fresh()->stock)->toBe(7);
});

test('RecordSaleJournal creates journal with debit Kas and credit Penjualan', function () {
    app(\App\Domains\Accounting\Services\JournalService::class)->ensureDefaultAccounts($this->tenant);
    $product = Product::factory()->create(['tenant_id' => $this->tenant->id, 'stock' => 10, 'sell_price' => 25000]);

    $sale = app(SaleService::class)->createSale(
        customerName: null,
        items: [['product_id' => $product->id, 'qty' => 1, 'unit_price' => 25000]],
        amount: 25000,
        paymentMethod: 'cash'
    );

    $journal = \App\Domains\Accounting\Models\Journal::withoutGlobalScopes()
        ->where('tenant_id', $this->tenant->id)
        ->where('reference_type', Sale::class)
        ->where('reference_id', $sale->id)
        ->first();

    expect($journal)->not->toBeNull()
        ->and($journal->items)->toHaveCount(2);

    $debits = $journal->items->where('debit', '>', 0);
    $credits = $journal->items->where('credit', '>', 0);
    expect($debits->sum('debit'))->toBe(25000.0)
        ->and($credits->sum('credit'))->toBe(25000.0);
});
