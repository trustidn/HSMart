<?php

use App\Domains\Subscription\Models\Subscription;
use App\Domains\Tenant\Models\Tenant;
use App\Models\User;

beforeEach(function () {
    $tenant = Tenant::factory()->create();
    $tenant->setting()->create([
        'store_name' => $tenant->name,
        'currency' => 'IDR',
        'timezone' => 'Asia/Jakarta',
    ]);
    Subscription::factory()->active()->create(['tenant_id' => $tenant->id]);
    $this->user = User::factory()->forTenant($tenant)->create();
    $this->tenant = $tenant;
    $this->actingAs($this->user);
});

test('sales report pdf export returns download', function () {
    $from = now()->startOfMonth()->format('Y-m-d');
    $to = now()->format('Y-m-d');

    $response = $this->get(route('reports.export.sales.pdf', [
        'date_from' => $from,
        'date_to' => $to,
    ]));

    $response->assertOk();
    expect($response->headers->get('Content-Disposition'))->toContain('attachment')->toContain('.pdf');
});

test('sales report excel export returns download', function () {
    $from = now()->startOfMonth()->format('Y-m-d');
    $to = now()->format('Y-m-d');

    $response = $this->get(route('reports.export.sales.excel', [
        'date_from' => $from,
        'date_to' => $to,
    ]));

    $response->assertOk();
    expect($response->headers->get('Content-Disposition'))->toContain('attachment')->toContain('.csv');
});

test('stock report pdf export returns download', function () {
    $response = $this->get(route('reports.export.stock.pdf', ['low_stock_only' => false]));

    $response->assertOk();
    expect($response->headers->get('Content-Disposition'))->toContain('attachment')->toContain('.pdf');
});
