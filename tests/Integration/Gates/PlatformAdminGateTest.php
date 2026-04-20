<?php

use App\Models\Staff\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('platform-admin gate allows platform admins', function () {
    $admin = User::factory()->platformAdmin()->create();

    expect(Gate::forUser($admin)->allows('platform-admin'))->toBeTrue();
});

test('platform-admin gate denies non-admin users', function () {
    $user = User::factory()->owner()->create();

    expect(Gate::forUser($user)->allows('platform-admin'))->toBeFalse();
});
