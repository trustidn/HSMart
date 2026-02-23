<?php

use App\Domains\Subscription\Models\Subscription;
use App\Domains\Tenant\Models\Tenant;
use App\Models\User;

test('user with expired subscription can view dashboard but is redirected when visiting pos', function () {
    $tenant = Tenant::factory()->create();
    $tenant->setting()->create([
        'store_name' => $tenant->name,
        'currency' => 'IDR',
        'timezone' => 'Asia/Jakarta',
    ]);
    Subscription::factory()->expired()->create(['tenant_id' => $tenant->id]);
    $user = User::factory()->forTenant($tenant)->create();
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertOk();

    $response = $this->get(route('pos'));
    $response->assertRedirect(route('subscription.expired'));
});

test('user with expired subscription is redirected when visiting purchase create', function () {
    $tenant = Tenant::factory()->create();
    $tenant->setting()->create([
        'store_name' => $tenant->name,
        'currency' => 'IDR',
        'timezone' => 'Asia/Jakarta',
    ]);
    Subscription::factory()->expired()->create(['tenant_id' => $tenant->id]);
    $user = User::factory()->forTenant($tenant)->create();
    $this->actingAs($user);

    $response = $this->get(route('purchasing.purchases.create'));
    $response->assertRedirect(route('subscription.expired'));
});

test('subscription expired page is viewable by user with expired subscription', function () {
    $tenant = Tenant::factory()->create();
    $tenant->setting()->create([
        'store_name' => $tenant->name,
        'currency' => 'IDR',
        'timezone' => 'Asia/Jakarta',
    ]);
    Subscription::factory()->expired()->create(['tenant_id' => $tenant->id]);
    $user = User::factory()->forTenant($tenant)->create();
    $this->actingAs($user);

    $response = $this->get(route('subscription.expired'));
    $response->assertOk();
});
