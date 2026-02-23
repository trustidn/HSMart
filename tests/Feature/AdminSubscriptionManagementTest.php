<?php

use App\Domains\Subscription\Models\Subscription;
use App\Domains\Subscription\Services\SubscriptionService;
use App\Domains\Tenant\Models\Tenant;

beforeEach(function () {
    $this->tenant = Tenant::factory()->create();
    $this->tenant->setting()->create(['store_name' => $this->tenant->name, 'currency' => 'IDR', 'timezone' => 'Asia/Jakarta']);
});

test('addSubscription creates subscription with duration and status', function () {
    $service = app(SubscriptionService::class);
    $sub = $service->addSubscription($this->tenant, 30, ['status' => Subscription::STATUS_ACTIVE]);

    expect($sub->tenant_id)->toBe($this->tenant->id)
        ->and($sub->duration_days)->toBe(30)
        ->and($sub->status)->toBe(Subscription::STATUS_ACTIVE);
    expect($sub->started_at->diffInDays($sub->ends_at))->toEqual(30);
});

test('extendSubscription adds days to ends_at', function () {
    $sub = Subscription::factory()->create([
        'tenant_id' => $this->tenant->id,
        'ends_at' => now()->addDays(5),
        'status' => Subscription::STATUS_ACTIVE,
    ]);
    $service = app(SubscriptionService::class);

    $service->extendSubscription($sub, 10);

    $sub->refresh();
    expect($sub->started_at->diffInDays($sub->ends_at))->toEqual(15);
});
