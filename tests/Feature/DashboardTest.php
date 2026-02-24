<?php

use App\Domains\Subscription\Models\Subscription;
use App\Domains\Tenant\Models\Tenant;
use App\Models\User;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('authenticated users with tenant and active subscription can visit the dashboard', function () {
    $tenant = Tenant::factory()->create();
    $tenant->setting()->create([
        'store_name' => $tenant->name,
        'currency' => 'IDR',
        'timezone' => 'Asia/Jakarta',
    ]);
    Subscription::factory()->active()->create(['tenant_id' => $tenant->id]);
    $user = User::factory()->forTenant($tenant)->create(['name' => 'John Doe']);
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertOk();
    $response->assertSee('John');
    $response->assertSee('Total products');
    $response->assertSee('Recent transactions');
});

test('authenticated users without tenant (superadmin) can visit the dashboard', function () {
    $user = User::factory()->create(['tenant_id' => null]);
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertOk();
});
