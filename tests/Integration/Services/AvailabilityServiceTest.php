<?php

use App\Models\Operations\BlockedDate;
use App\Services\Scheduling\AvailabilityService;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('returns availability for next 30 days', function () {
    $result = resolve(AvailabilityService::class)->getAvailability();

    expect($result)->toBeArray()
        ->toHaveCount(30)
        ->and($result[0])->toHaveKeys(['date', 'available', 'reason', 'remaining_capacity']);
});

test('blocked date shows as unavailable', function () {
    BlockedDate::factory()->create([
        'date' => now()->addDay()->toDateString(),
        'is_all_day' => true,
        'reason' => 'Holiday',
    ]);

    $result = resolve(AvailabilityService::class)->getAvailability();
    $tomorrow = collect($result)->firstOrFail(
        fn (array $day): bool => $day['date'] === now()->addDay()->toDateString(),
    );

    expect($tomorrow['available'])->toBeFalse()
        ->and($tomorrow['remaining_capacity'])->toBe(0);
});
