<?php

use App\Domains\Subscription\Models\Subscription;
use App\Domains\Subscription\Services\SubscriptionService;
use App\Domains\Tenant\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->service = app(SubscriptionService::class);
});

test('isActive returns false when tenant is null', function () {
    expect($this->service->isActive(null))->toBeFalse();
});

test('isActive returns false when tenant has no current subscription', function () {
    $tenant = Tenant::factory()->create();
    Subscription::factory()->expired()->create(['tenant_id' => $tenant->id]);

    expect($this->service->isActive($tenant))->toBeFalse();
});

test('isActive returns true when tenant has active subscription', function () {
    $tenant = Tenant::factory()->create();
    Subscription::factory()->active()->create(['tenant_id' => $tenant->id]);

    expect($this->service->isActive($tenant))->toBeTrue();
});

test('canCreateSale and canCreatePurchase follow isActive', function () {
    $tenant = Tenant::factory()->create();
    Subscription::factory()->active()->create(['tenant_id' => $tenant->id]);

    expect($this->service->canCreateSale($tenant))->toBeTrue()
        ->and($this->service->canCreatePurchase($tenant))->toBeTrue();

    Subscription::query()->where('tenant_id', $tenant->id)->delete();
    Subscription::factory()->expired()->create(['tenant_id' => $tenant->id]);

    expect($this->service->canCreateSale($tenant))->toBeFalse()
        ->and($this->service->canCreatePurchase($tenant))->toBeFalse();
});
