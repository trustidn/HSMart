<?php

use App\Domains\Subscription\Models\Subscription;
use App\Domains\Tenant\Models\Tenant;
use App\Models\User;

beforeEach(function () {
    $this->tenant = Tenant::factory()->create();
    $this->tenant->setting()->create(['store_name' => $this->tenant->name, 'currency' => 'IDR', 'timezone' => 'Asia/Jakarta']);
    Subscription::factory()->active()->create(['tenant_id' => $this->tenant->id]);
});

test('tenant owner can access users list and create page', function () {
    $owner = User::factory()->forTenant($this->tenant)->tenantOwner()->create();
    $this->actingAs($owner);

    $this->get(route('users.index'))->assertOk();
    $this->get(route('users.create'))->assertOk();
});

test('tenant owner can create member', function () {
    $owner = User::factory()->forTenant($this->tenant)->tenantOwner()->create();
    $this->actingAs($owner);

    \Livewire\Livewire::test(\App\Domains\Tenant\Livewire\TenantUserForm::class)
        ->set('name', 'New Member')
        ->set('email', 'member@tenant.com')
        ->set('password', 'password')
        ->set('password_confirmation', 'password')
        ->call('save');

    $user = User::where('email', 'member@tenant.com')->first();
    expect($user)->not->toBeNull()
        ->and($user->tenant_id)->toBe($this->tenant->id)
        ->and($user->is_tenant_owner)->toBeFalse();
});

test('tenant owner can update member', function () {
    $owner = User::factory()->forTenant($this->tenant)->tenantOwner()->create();
    $member = User::factory()->forTenant($this->tenant)->create(['name' => 'Old', 'email' => 'oldmember@test.com']);
    $this->actingAs($owner);
    app()->instance('tenant', $this->tenant);

    \Livewire\Livewire::test(\App\Domains\Tenant\Livewire\TenantUserForm::class, ['userId' => $member->id])
        ->set('name', 'Updated Member')
        ->set('email', 'updated@test.com')
        ->call('save');

    $member->refresh();
    expect($member->name)->toBe('Updated Member')
        ->and($member->email)->toBe('updated@test.com');
});

test('tenant owner can delete member', function () {
    $owner = User::factory()->forTenant($this->tenant)->tenantOwner()->create();
    $member = User::factory()->forTenant($this->tenant)->create();
    $this->actingAs($owner);
    app()->instance('tenant', $this->tenant);

    \Livewire\Livewire::test(\App\Domains\Tenant\Livewire\TenantUserList::class)
        ->call('confirmDelete', $member->id)
        ->call('deleteUser');

    expect(User::find($member->id))->toBeNull();
});

test('tenant member cannot access users index', function () {
    $member = User::factory()->forTenant($this->tenant)->create();

    $this->actingAs($member)->get(route('users.index'))->assertForbidden();
});
