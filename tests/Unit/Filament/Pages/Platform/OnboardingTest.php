<?php

use App\Enums\Staff\UserRole;
use App\Filament\Pages\Platform\Onboarding;
use App\Models\Staff\User;

test('onboarding page exposes its Filament metadata', function () {
    $reflection = new ReflectionClass(Onboarding::class);

    expect($reflection->getStaticPropertyValue('title'))->toBe('Welcome to KneadIt')
        ->and(Onboarding::shouldRegisterNavigation())->toBeFalse();
});

test('onboarding page access follows the user role', function (?UserRole $role, bool $expected) {
    if ($role !== null) {
        $this->actingAs(new User(['role' => $role]));
    }

    expect(Onboarding::canAccess())->toBe($expected);
})->with([
    'owner' => [UserRole::Owner, true],
    'manager' => [UserRole::Manager, true],
    'staff' => [UserRole::Staff, false],
    'guest' => [null, false],
]);
