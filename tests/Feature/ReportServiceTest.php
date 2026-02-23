<?php

use App\Domains\POS\Services\SaleService;
use App\Domains\Product\Models\Product;
use App\Domains\Reporting\Services\ReportService;
use App\Domains\Subscription\Models\Subscription;
use App\Domains\Tenant\Models\Tenant;

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

test('getRingkasanOmzet returns total and count for period', function () {
    $product = Product::factory()->create(['tenant_id' => $this->tenant->id, 'stock' => 100, 'sell_price' => 5000]);
    app(SaleService::class)->createSale(
        customerName: null,
        items: [['product_id' => $product->id, 'qty' => 2, 'unit_price' => 5000]],
        amount: 10000,
        paymentMethod: 'cash'
    );
    app(SaleService::class)->createSale(
        customerName: null,
        items: [['product_id' => $product->id, 'qty' => 1, 'unit_price' => 5000]],
        amount: 5000,
        paymentMethod: 'cash'
    );

    $from = now()->subDays(1)->format('Y-m-d');
    $to = now()->addDay()->format('Y-m-d');
    $result = app(ReportService::class)->getRingkasanOmzet($from, $to);

    expect($result['total'])->toBe(15000.0)
        ->and($result['count'])->toBe(2)
        ->and($result['from'])->toBe($from)
        ->and($result['to'])->toBe($to);
});

test('getLaporanPenjualan returns paginated sales', function () {
    $product = Product::factory()->create(['tenant_id' => $this->tenant->id, 'stock' => 10, 'sell_price' => 1000]);
    app(SaleService::class)->createSale(
        customerName: 'Customer A',
        items: [['product_id' => $product->id, 'qty' => 1, 'unit_price' => 1000]],
        amount: 1000,
        paymentMethod: 'cash'
    );

    $from = now()->subDays(1)->format('Y-m-d');
    $to = now()->addDay()->format('Y-m-d');
    $paginator = app(ReportService::class)->getLaporanPenjualan($from, $to, 5);

    expect($paginator->count())->toBe(1)
        ->and($paginator->first()->sale_number)->toStartWith('INV-')
        ->and($paginator->first()->customer_name)->toBe('Customer A');
});

test('getTopProduk returns products by quantity sold', function () {
    $p1 = Product::factory()->create(['tenant_id' => $this->tenant->id, 'stock' => 100, 'sell_price' => 1000]);
    $p2 = Product::factory()->create(['tenant_id' => $this->tenant->id, 'stock' => 100, 'sell_price' => 2000]);
    app(SaleService::class)->createSale(
        customerName: null,
        items: [
            ['product_id' => $p1->id, 'qty' => 5, 'unit_price' => 1000],
            ['product_id' => $p2->id, 'qty' => 2, 'unit_price' => 2000],
        ],
        amount: 9000,
        paymentMethod: 'cash'
    );

    $from = now()->subDays(1)->format('Y-m-d');
    $to = now()->addDay()->format('Y-m-d');
    $top = app(ReportService::class)->getTopProduk($from, $to, 10);

    expect($top)->toHaveCount(2);
    $first = $top->first();
    expect($first->product_id)->toBe($p1->id)
        ->and($first->total_qty)->toBe(5)
        ->and($first->total_revenue)->toBe(5000.0);
});

test('getLaporanStok returns products and lowStockOnly filters correctly', function () {
    Product::factory()->create(['tenant_id' => $this->tenant->id, 'stock' => 10, 'minimum_stock' => 5]); // low: 10 <= 5 is false
    Product::factory()->create(['tenant_id' => $this->tenant->id, 'stock' => 3, 'minimum_stock' => 5]);  // low: 3 <= 5 is true
    Product::factory()->create(['tenant_id' => $this->tenant->id, 'stock' => 100, 'minimum_stock' => 0]); // min 0 = not in low filter

    $all = app(ReportService::class)->getLaporanStok(false);
    $lowOnly = app(ReportService::class)->getLaporanStok(true);

    expect($all)->toHaveCount(3)
        ->and($lowOnly)->toHaveCount(1)
        ->and($lowOnly->first()->stock)->toBe(3);
});

test('getLabaRugi returns revenue, cogs and gross profit', function () {
    $product = Product::factory()->create([
        'tenant_id' => $this->tenant->id,
        'stock' => 10,
        'sell_price' => 10000,
        'cost_price' => 6000,
    ]);
    app(SaleService::class)->createSale(
        customerName: null,
        items: [['product_id' => $product->id, 'qty' => 2, 'unit_price' => 10000]],
        amount: 20000,
        paymentMethod: 'cash'
    );

    $from = now()->subDays(1)->format('Y-m-d');
    $to = now()->addDay()->format('Y-m-d');
    $result = app(ReportService::class)->getLabaRugi($from, $to);

    expect($result['revenue'])->toBe(20000.0)
        ->and($result['cogs'])->toBe(12000.0)
        ->and($result['gross_profit'])->toBe(8000.0);
});
