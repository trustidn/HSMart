<?php

use App\Domains\Tenant\Models\Tenant;
use App\Models\User;

beforeEach(function () {
    $this->superadmin = User::factory()->create(['tenant_id' => null]);
    $this->actingAs($this->superadmin);
});

test('superadmin can access user list and create page', function () {
    $this->get(route('admin.users'))->assertOk();
    $this->get(route('admin.users.create'))->assertOk();
});

test('superadmin can create user with tenant and as owner', function () {
    $tenant = Tenant::factory()->create();
    $tenant->setting()->create(['store_name' => $tenant->name, 'currency' => 'IDR', 'timezone' => 'Asia/Jakarta']);

    \Livewire\Livewire::test(\App\Domains\Tenant\Livewire\Admin\UserForm::class)
        ->set('name', 'Owner User')
        ->set('email', 'owner@test.com')
        ->set('password', 'password')
        ->set('password_confirmation', 'password')
        ->set('tenant_id', $tenant->id)
        ->set('is_tenant_owner', true)
        ->call('save');

    $user = User::where('email', 'owner@test.com')->first();
    expect($user)->not->toBeNull()
        ->and($user->tenant_id)->toBe($tenant->id)
        ->and($user->is_tenant_owner)->toBeTrue();
});

test('superadmin can create user as member', function () {
    $tenant = Tenant::factory()->create();
    $tenant->setting()->create(['store_name' => $tenant->name, 'currency' => 'IDR', 'timezone' => 'Asia/Jakarta']);

    \Livewire\Livewire::test(\App\Domains\Tenant\Livewire\Admin\UserForm::class)
        ->set('name', 'Member User')
        ->set('email', 'member@test.com')
        ->set('password', 'password')
        ->set('password_confirmation', 'password')
        ->set('tenant_id', $tenant->id)
        ->set('is_tenant_owner', false)
        ->call('save');

    $user = User::where('email', 'member@test.com')->first();
    expect($user)->not->toBeNull()
        ->and($user->tenant_id)->toBe($tenant->id)
        ->and($user->is_tenant_owner)->toBeFalse();
});

test('superadmin can update user', function () {
    $tenant = Tenant::factory()->create();
    $user = User::factory()->forTenant($tenant)->create(['name' => 'Old', 'email' => 'old@test.com']);

    \Livewire\Livewire::test(\App\Domains\Tenant\Livewire\Admin\UserForm::class, ['userId' => $user->id])
        ->set('name', 'Updated Name')
        ->set('email', 'updated@test.com')
        ->call('save');

    $user->refresh();
    expect($user->name)->toBe('Updated Name')
        ->and($user->email)->toBe('updated@test.com');
});

test('superadmin can delete user', function () {
    $user = User::factory()->create(['tenant_id' => null]);

    \Livewire\Livewire::test(\App\Domains\Tenant\Livewire\Admin\UserList::class)
        ->call('confirmDelete', $user->id)
        ->call('deleteUser');

    expect(User::find($user->id))->toBeNull();
});

test('tenant user cannot access admin user create', function () {
    $tenant = Tenant::factory()->create();
    $tenant->setting()->create(['store_name' => $tenant->name, 'currency' => 'IDR', 'timezone' => 'Asia/Jakarta']);
    $user = User::factory()->forTenant($tenant)->create();

    $this->actingAs($user)->get(route('admin.users.create'))->assertForbidden();
});
