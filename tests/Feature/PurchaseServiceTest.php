<?php

use App\Domains\Accounting\Models\Journal;
use App\Domains\Product\Models\Product;
use App\Domains\Purchasing\Events\PurchaseCompleted;
use App\Domains\Purchasing\Models\Purchase;
use App\Domains\Purchasing\Models\Supplier;
use App\Domains\Purchasing\Services\PurchaseService;
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

test('PurchaseService creates purchase with items and dispatches PurchaseCompleted', function () {
    Event::fake([PurchaseCompleted::class]);
    $supplier = Supplier::factory()->create(['tenant_id' => $this->tenant->id]);
    $product = Product::factory()->create(['tenant_id' => $this->tenant->id, 'stock' => 5]);

    $service = app(PurchaseService::class);
    $purchase = $service->createPurchase(
        $supplier->id,
        [
            ['product_id' => $product->id, 'qty' => 3, 'unit_cost' => 10000],
        ]
    );

    expect($purchase)->toBeInstanceOf(Purchase::class)
        ->and($purchase->purchase_number)->toStartWith('PO-')
        ->and((float) $purchase->total_amount)->toBe(30000.0)
        ->and($purchase->items)->toHaveCount(1)
        ->and($purchase->supplier_id)->toBe($supplier->id);
    Event::assertDispatched(PurchaseCompleted::class);
});

test('AddPurchaseStock listener increases product stock', function () {
    $supplier = Supplier::factory()->create(['tenant_id' => $this->tenant->id]);
    $product = Product::factory()->create(['tenant_id' => $this->tenant->id, 'stock' => 10]);
    $purchase = Purchase::withoutGlobalScopes()->create([
        'tenant_id' => $this->tenant->id,
        'supplier_id' => $supplier->id,
        'purchase_number' => 'PO-000001',
        'purchase_date' => now(),
        'total_amount' => 50000,
        'status' => 'completed',
    ]);
    $purchase->items()->create([
        'product_id' => $product->id,
        'qty' => 4,
        'unit_cost' => 12500,
        'subtotal' => 50000,
    ]);

    event(new PurchaseCompleted($purchase));

    expect($product->fresh()->stock)->toBe(14);
});

test('RecordPurchaseJournal creates journal with debit Persediaan and credit Hutang', function () {
    app(\App\Domains\Accounting\Services\JournalService::class)->ensureDefaultAccounts($this->tenant);
    $supplier = Supplier::factory()->create(['tenant_id' => $this->tenant->id]);
    $product = Product::factory()->create(['tenant_id' => $this->tenant->id, 'stock' => 20]);

    $purchase = app(PurchaseService::class)->createPurchase(
        $supplier->id,
        [
            ['product_id' => $product->id, 'qty' => 2, 'unit_cost' => 15000],
        ]
    );

    $journal = Journal::withoutGlobalScopes()
        ->where('tenant_id', $this->tenant->id)
        ->where('reference_type', Purchase::class)
        ->where('reference_id', $purchase->id)
        ->first();

    expect($journal)->not->toBeNull()
        ->and($journal->items)->toHaveCount(2);

    $debits = $journal->items->where('debit', '>', 0);
    $credits = $journal->items->where('credit', '>', 0);
    expect($debits->sum('debit'))->toBe(30000.0)
        ->and($credits->sum('credit'))->toBe(30000.0);
});

test('PurchaseService throws when subscription expired', function () {
    $tenant = Tenant::factory()->create();
    $tenant->setting()->create(['store_name' => $tenant->name, 'currency' => 'IDR', 'timezone' => 'Asia/Jakarta']);
    Subscription::factory()->expired()->create(['tenant_id' => $tenant->id]);
    app()->instance('tenant', $tenant);
    $supplier = Supplier::factory()->create(['tenant_id' => $tenant->id]);
    $product = Product::factory()->create(['tenant_id' => $tenant->id]);
    $service = app(PurchaseService::class);

    $service->createPurchase(
        $supplier->id,
        [['product_id' => $product->id, 'qty' => 1, 'unit_cost' => 1000]]
    );
})->throws(\DomainException::class);

test('PurchaseService throws when supplier not found', function () {
    $product = Product::factory()->create(['tenant_id' => $this->tenant->id]);
    $service = app(PurchaseService::class);

    $service->createPurchase(
        99999,
        [['product_id' => $product->id, 'qty' => 1, 'unit_cost' => 1000]]
    );
})->throws(\InvalidArgumentException::class);
