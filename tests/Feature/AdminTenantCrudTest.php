<?php

use App\Domains\Subscription\Models\Subscription;
use App\Domains\Tenant\Models\Tenant;
use App\Domains\Tenant\Services\TenantService;
use App\Models\User;

beforeEach(function () {
    $this->superadmin = User::factory()->create(['tenant_id' => null]);
    $this->actingAs($this->superadmin);
});

test('superadmin can access tenant list and create page', function () {
    $this->get(route('admin.tenants'))->assertOk();
    $this->get(route('admin.tenants.create'))->assertOk();
});

test('superadmin can create tenant', function () {
    $response = \Livewire\Livewire::test(\App\Domains\Tenant\Livewire\Admin\TenantForm::class)
        ->set('name', 'Toko Baru')
        ->call('save');

    $response->assertRedirect(route('admin.tenants'));
    expect(Tenant::where('name', 'Toko Baru')->exists())->toBeTrue();
    $tenant = Tenant::where('name', 'Toko Baru')->first();
    expect($tenant->subscriptions()->count())->toBe(1)
        ->and($tenant->setting)->not->toBeNull();
});

test('superadmin can update tenant', function () {
    $tenant = Tenant::factory()->create(['name' => 'Old', 'slug' => 'old']);
    $tenant->setting()->create(['store_name' => 'Old', 'currency' => 'IDR', 'timezone' => 'Asia/Jakarta']);
    Subscription::factory()->active()->create(['tenant_id' => $tenant->id]);

    \Livewire\Livewire::test(\App\Domains\Tenant\Livewire\Admin\TenantForm::class, ['tenantId' => $tenant->id])
        ->set('name', 'Updated Name')
        ->set('slug', 'updated-slug')
        ->call('save');

    $tenant->refresh();
    expect($tenant->name)->toBe('Updated Name')
        ->and($tenant->slug)->toBe('updated-slug');
});

test('superadmin can delete tenant', function () {
    $tenant = Tenant::factory()->create();
    $tenant->setting()->create(['store_name' => $tenant->name, 'currency' => 'IDR', 'timezone' => 'Asia/Jakarta']);
    $user = User::factory()->create(['tenant_id' => $tenant->id]);

    app(TenantService::class)->delete($tenant);

    expect(Tenant::find($tenant->id))->toBeNull();
    $user->refresh();
    expect($user->tenant_id)->toBeNull();
});

test('tenant user cannot access admin tenant create', function () {
    $tenant = Tenant::factory()->create();
    $tenant->setting()->create(['store_name' => $tenant->name, 'currency' => 'IDR', 'timezone' => 'Asia/Jakarta']);
    Subscription::factory()->active()->create(['tenant_id' => $tenant->id]);
    $user = User::factory()->create(['tenant_id' => $tenant->id]);

    $this->actingAs($user)->get(route('admin.tenants.create'))->assertForbidden();
});
