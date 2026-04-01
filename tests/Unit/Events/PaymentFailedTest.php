<?php

use App\Events\Platform\PaymentFailed;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('it can be constructed with user, tenant, and amount', function () {
    $user = User::factory()->create();

    $event = new PaymentFailed($user, null, 29.00);

    expect($event->user)->toBe($user)
        ->and($event->tenant)->toBeNull()
        ->and($event->amount)->toBe(29.00);
});
