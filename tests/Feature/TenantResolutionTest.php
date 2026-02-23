<?php

use App\Domains\Subscription\Models\Subscription;
use App\Domains\Tenant\Models\Tenant;
use App\Models\User;

test('tenant context is set when user has tenant_id', function () {
    $tenant = Tenant::factory()->create();
    $tenant->setting()->create([
        'store_name' => $tenant->name,
        'currency' => 'IDR',
        'timezone' => 'Asia/Jakarta',
    ]);
    Subscription::factory()->active()->create(['tenant_id' => $tenant->id]);
    $user = User::factory()->forTenant($tenant)->create();
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertOk();
    expect(tenant())->not->toBeNull()
        ->and(tenant()->id)->toBe($tenant->id);
});

test('user without tenant_id gets 403 on dashboard', function () {
    $user = User::factory()->create(['tenant_id' => null]);
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertForbidden();
});
