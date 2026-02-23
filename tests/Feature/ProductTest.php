<?php

use App\Domains\Product\Models\Product;
use App\Domains\Subscription\Models\Subscription;
use App\Domains\Tenant\Models\Tenant;
use App\Models\User;
use Livewire\Livewire;

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
    app()->instance('tenant', $tenant);
    $this->actingAs($this->user);
});

test('product index page shows products', function () {
    Product::factory()->count(2)->create(['tenant_id' => $this->tenant->id]);

    $response = $this->get(route('products.index'));
    $response->assertOk();
    Livewire::test(\App\Domains\Product\Livewire\ProductIndex::class)
        ->assertOk();
});

test('can create product', function () {
    Livewire::test(\App\Domains\Product\Livewire\ProductForm::class)
        ->set('sku', 'SKU-001')
        ->set('name', 'Test Product')
        ->set('cost_price', '10000')
        ->set('sell_price', '15000')
        ->set('minimum_stock', '5')
        ->call('save')
        ->assertRedirect(route('products.index'));

    $this->assertDatabaseHas('products', [
        'tenant_id' => $this->tenant->id,
        'sku' => 'SKU-001',
        'name' => 'Test Product',
    ]);
});

test('cannot create product with duplicate sku', function () {
    Product::factory()->create(['tenant_id' => $this->tenant->id, 'sku' => 'DUP-001']);

    Livewire::test(\App\Domains\Product\Livewire\ProductForm::class)
        ->set('sku', 'DUP-001')
        ->set('name', 'Another')
        ->set('cost_price', '1')
        ->set('sell_price', '2')
        ->call('save')
        ->assertHasErrors('sku');
});

test('can update product', function () {
    $product = Product::factory()->create(['tenant_id' => $this->tenant->id, 'name' => 'Old Name']);

    Livewire::test(\App\Domains\Product\Livewire\ProductForm::class, ['productId' => $product->id])
        ->set('name', 'Updated Name')
        ->call('save')
        ->assertRedirect(route('products.index'));

    expect($product->fresh()->name)->toBe('Updated Name');
});

test('can adjust stock via ProductService', function () {
    $product = Product::factory()->create(['tenant_id' => $this->tenant->id, 'stock' => 10]);

    app(\App\Domains\Product\Services\ProductService::class)->adjustStock($product, 25);

    expect($product->fresh()->stock)->toBe(25);
});
