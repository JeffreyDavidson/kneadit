<?php

use App\Enums\Staff\UserRole;
use App\Models\Platform\Tenant;
use App\Models\Staff\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\Gate;

beforeEach(function () {
    setUpCentralTest();
    test()->tenant = createTenantWithDomain('sweet-bakes');
});

function tenantAbilityTarget(string $ability): Tenant|string
{
    return in_array($ability, ['viewAny', 'create', 'deleteAny'], true)
        ? Tenant::class
        : test()->tenant;
}

test('platform admins can manage bakeries', function (string $ability) {
    $admin = User::factory()->platformAdmin()->create();

    expect($admin->can($ability, tenantAbilityTarget($ability)))->toBeTrue();
})->with(['viewAny', 'view', 'create', 'update', 'delete', 'deleteAny']);

test('everyone else is denied every bakery ability', function (UserRole $role, string $ability) {
    $user = User::factory()->create(['role' => $role]);

    expect($user->can($ability, tenantAbilityTarget($ability)))->toBeFalse();
})->with([UserRole::Owner, UserRole::Manager])
    ->with(['viewAny', 'view', 'create', 'update', 'delete', 'deleteAny']);

test('a platform admin cannot delete a bakery whose owner has a valid subscription', function () {
    $owner = User::factory()->owner()->create();
    $owner->subscriptions()->create([
        'type' => 'default',
        'stripe_id' => 'sub_test_123',
        'stripe_status' => 'active',
        'stripe_price' => 'price_starter_test',
    ]);
    $tenant = createTenantWithDomain('subscribed', attributes: ['user_id' => $owner->id]);
    $admin = User::factory()->platformAdmin()->create();

    $response = Gate::forUser($admin)->inspect('delete', $tenant);

    expect($response)->toBeInstanceOf(Response::class)
        ->and($response->denied())->toBeTrue()
        ->and($response->message())->toBe("Cancel this bakery's subscription first.");
});
