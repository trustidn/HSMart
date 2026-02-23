<?php

use App\Domains\POS\Livewire\PosPage;
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

test('pos page loads for authenticated tenant user', function () {
    $response = $this->get(route('pos'));
    $response->assertOk();
});

test('product search dropdown shows matching products when typing name or sku', function () {
    $product = Product::factory()->create([
        'tenant_id' => $this->tenant->id,
        'name' => 'Kopi Susu Premium',
        'sku' => 'KP-001',
        'is_active' => true,
    ]);

    $component = Livewire::test(PosPage::class)
        ->set('barcodeInput', 'Kopi');

    $component->assertSee('Kopi Susu Premium');
    $component->assertSee('KP-001');
});

test('selecting product from dropdown adds to cart', function () {
    $product = Product::factory()->create([
        'tenant_id' => $this->tenant->id,
        'name' => 'Teh Botol',
        'sku' => 'TB-001',
        'sell_price' => 5000,
        'is_active' => true,
    ]);

    Livewire::test(PosPage::class)
        ->call('selectProduct', $product->id)
        ->assertSet('cart.0.product_id', $product->id)
        ->assertSet('cart.0.name', 'Teh Botol')
        ->assertSet('cart.0.qty', 1)
        ->assertSet('cart.0.unit_price', 5000.0)
        ->assertSet('barcodeInput', '');
});
