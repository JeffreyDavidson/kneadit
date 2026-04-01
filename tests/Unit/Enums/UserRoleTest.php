<?php

use App\Enums\Staff\UserRole;

test('has expected cases', function () {
    expect(UserRole::cases())
        ->toHaveCount(4)
        ->and(UserRole::Staff->value)->toBe('staff')
        ->and(UserRole::Manager->value)->toBe('manager')
        ->and(UserRole::Owner->value)->toBe('owner')
        ->and(UserRole::PlatformAdmin->value)->toBe('platform_admin');
});

test('hierarchy is enforced via meetsRequirement', function () {
    expect(UserRole::Owner->meetsRequirement(UserRole::Staff))->toBeTrue()
        ->and(UserRole::Owner->meetsRequirement(UserRole::Manager))->toBeTrue()
        ->and(UserRole::Owner->meetsRequirement(UserRole::Owner))->toBeTrue()
        ->and(UserRole::Manager->meetsRequirement(UserRole::Staff))->toBeTrue()
        ->and(UserRole::Manager->meetsRequirement(UserRole::Manager))->toBeTrue()
        ->and(UserRole::Manager->meetsRequirement(UserRole::Owner))->toBeFalse()
        ->and(UserRole::Staff->meetsRequirement(UserRole::Manager))->toBeFalse()
        ->and(UserRole::Staff->meetsRequirement(UserRole::Owner))->toBeFalse()
        ->and(UserRole::PlatformAdmin->meetsRequirement(UserRole::Owner))->toBeTrue();
});
