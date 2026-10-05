<?php

use App\Actions\Platform\ConsumeBillingHandoffToken;
use App\Exceptions\Platform\BillingHandoffRefusedException;
use App\Models\Platform\BillingHandoffToken;
use App\Models\Platform\Tenant;
use App\Models\Staff\User;

beforeEach(function () {
    setUpCentralTest();

    test()->owner = User::factory()->owner()->create();
    test()->tenant = Tenant::factory()->create(['id' => 'sunrise', 'user_id' => test()->owner->id]);
});

test('claims a valid token and returns the bakery owner', function () {
    BillingHandoffToken::factory()
        ->forToken('valid-token')
        ->for(test()->tenant)
        ->for(test()->owner)
        ->create();

    $user = resolve(ConsumeBillingHandoffToken::class)('valid-token', '10.0.0.1');

    $record = BillingHandoffToken::query()->sole();

    expect($user->id)->toBe(test()->owner->id)
        ->and($record->consumed_at)->not->toBeNull()
        ->and($record->consumer_ip)->toBe('10.0.0.1');
});

test('refuses a token that was already used', function () {
    BillingHandoffToken::factory()
        ->forToken('used-token')
        ->consumed()
        ->for(test()->tenant)
        ->for(test()->owner)
        ->create();

    resolve(ConsumeBillingHandoffToken::class)('used-token');
})->throws(BillingHandoffRefusedException::class);

test('refuses an expired token', function () {
    BillingHandoffToken::factory()
        ->forToken('expired-token')
        ->expired()
        ->for(test()->tenant)
        ->for(test()->owner)
        ->create();

    resolve(ConsumeBillingHandoffToken::class)('expired-token');
})->throws(BillingHandoffRefusedException::class);

test('refuses an unknown token', function () {
    resolve(ConsumeBillingHandoffToken::class)('never-issued');
})->throws(BillingHandoffRefusedException::class);

test('a token can be consumed only once', function () {
    BillingHandoffToken::factory()
        ->forToken('single-use')
        ->for(test()->tenant)
        ->for(test()->owner)
        ->create();

    $action = resolve(ConsumeBillingHandoffToken::class);
    $action('single-use', '10.0.0.1');

    expect(fn () => $action('single-use', '10.0.0.2'))->toThrow(BillingHandoffRefusedException::class)
        ->and(BillingHandoffToken::query()->sole()->consumer_ip)->toBe('10.0.0.1');
});

test('a token for one bakery signs in that bakery owner, not another bakery owner', function () {
    $otherOwner = User::factory()->owner()->create();
    Tenant::factory()->create(['id' => 'moonrise', 'user_id' => $otherOwner->id]);

    BillingHandoffToken::factory()
        ->forToken('sunrise-token')
        ->for(test()->tenant)
        ->for(test()->owner)
        ->create();

    $user = resolve(ConsumeBillingHandoffToken::class)('sunrise-token');

    expect($user->id)->toBe(test()->owner->id)
        ->and($user->id)->not->toBe($otherOwner->id);
});

test('refuses a token whose bakery has since been handed to a different owner', function () {
    $newOwner = User::factory()->owner()->create();
    test()->tenant->update(['user_id' => $newOwner->id]);

    BillingHandoffToken::factory()
        ->forToken('stale-owner')
        ->for(test()->tenant)
        ->for(test()->owner)
        ->create();

    resolve(ConsumeBillingHandoffToken::class)('stale-owner');
})->throws(BillingHandoffRefusedException::class);

test('refuses a token whose bakery has no owner any more', function () {
    test()->tenant->update(['user_id' => null]);

    BillingHandoffToken::factory()
        ->forToken('orphaned')
        ->for(test()->tenant)
        ->for(test()->owner)
        ->create();

    resolve(ConsumeBillingHandoffToken::class)('orphaned');
})->throws(BillingHandoffRefusedException::class);

test('a refusal for a known token carries the bakery so the page can link back', function () {
    BillingHandoffToken::factory()
        ->forToken('used-token')
        ->consumed()
        ->for(test()->tenant)
        ->for(test()->owner)
        ->create();

    try {
        resolve(ConsumeBillingHandoffToken::class)('used-token');
    } catch (BillingHandoffRefusedException $exception) {
        expect($exception->tenant?->id)->toBe('sunrise');

        return;
    }

    throw new RuntimeException('Expected the handoff to be refused.');
});
