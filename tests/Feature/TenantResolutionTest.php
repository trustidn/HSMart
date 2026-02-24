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

test('user without tenant_id can visit dashboard (admin dashboard)', function () {
    $user = User::factory()->create(['tenant_id' => null]);
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertOk();
});

test('user without tenant_id is redirected to dashboard when visiting tenant route', function () {
    $user = User::factory()->create(['tenant_id' => null]);
    $this->actingAs($user);

    $response = $this->get(route('products.index'));
    $response->assertRedirect(route('dashboard'));
});

test('superadmin can access admin tenants page', function () {
    $user = User::factory()->create(['tenant_id' => null]);
    $this->actingAs($user);

    $response = $this->get(route('admin.tenants'));
    $response->assertOk();
});

test('tenant user cannot access admin tenants page', function () {
    $tenant = Tenant::factory()->create();
    $tenant->setting()->create([
        'store_name' => $tenant->name,
        'currency' => 'IDR',
        'timezone' => 'Asia/Jakarta',
    ]);
    Subscription::factory()->active()->create(['tenant_id' => $tenant->id]);
    $user = User::factory()->forTenant($tenant)->create();
    $this->actingAs($user);

    $response = $this->get(route('admin.tenants'));
    $response->assertForbidden();
});

test('tenant user cannot access settings profile page', function () {
    $tenant = Tenant::factory()->create();
    $tenant->setting()->create([
        'store_name' => $tenant->name,
        'currency' => 'IDR',
        'timezone' => 'Asia/Jakarta',
    ]);
    Subscription::factory()->active()->create(['tenant_id' => $tenant->id]);
    $user = User::factory()->forTenant($tenant)->create();
    $this->actingAs($user);

    $response = $this->get(route('profile.edit'));
    $response->assertForbidden();
});

test('tenant user can edit own profile via team users edit', function () {
    $tenant = Tenant::factory()->create();
    $tenant->setting()->create([
        'store_name' => $tenant->name,
        'currency' => 'IDR',
        'timezone' => 'Asia/Jakarta',
    ]);
    Subscription::factory()->active()->create(['tenant_id' => $tenant->id]);
    $user = User::factory()->forTenant($tenant)->create();
    $this->actingAs($user);

    $response = $this->get(route('users.edit', ['userId' => $user->id]));
    $response->assertOk();
});
