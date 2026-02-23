<?php

use App\Domains\Product\Models\Product;
use App\Domains\Tenant\Models\Tenant;
use App\Services\StockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->tenant = Tenant::factory()->create();
    app()->instance('tenant', $this->tenant);
    $this->product = Product::factory()->create(['tenant_id' => $this->tenant->id, 'stock' => 10]);
    $this->service = app(StockService::class);
});

test('increaseStock adds quantity', function () {
    $this->service->increaseStock($this->product, 5);
    expect($this->product->fresh()->stock)->toBe(15);
});

test('decreaseStock subtracts quantity', function () {
    $this->service->decreaseStock($this->product, 3);
    expect($this->product->fresh()->stock)->toBe(7);
});

test('decreaseStock throws when insufficient stock', function () {
    $this->service->decreaseStock($this->product, 20);
})->throws(\DomainException::class);

test('adjustStock sets stock to new value', function () {
    $this->service->adjustStock($this->product, 100);
    expect($this->product->fresh()->stock)->toBe(100);
});

test('adjustStock throws when negative', function () {
    $this->service->adjustStock($this->product, -1);
})->throws(\InvalidArgumentException::class);
