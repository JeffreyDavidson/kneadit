<?php

use App\ValueObjects\DateRange;

test('isActive returns true when now is within range', function () {
    $range = DateRange::fromStrings(
        now()->subDays(1)->toDateString(),
        now()->addDays(1)->toDateString(),
    );

    expect($range->isActive())->toBeTrue();
});

test('contains returns true for date within range', function () {
    $range = DateRange::fromStrings('2026-01-01', '2026-12-31');

    expect($range->contains('2026-06-15'))->toBeTrue()
        ->and($range->contains('2027-01-01'))->toBeFalse();
});
