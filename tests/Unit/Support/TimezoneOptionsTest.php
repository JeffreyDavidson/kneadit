<?php

use App\Support\TimezoneOptions;
use Illuminate\Support\Facades\Date;

beforeEach(fn () => Date::setTestNow('2026-07-01 12:00'));

test('every IANA identifier is offered exactly once', function () {
    $values = collect(TimezoneOptions::grouped())->flatMap(fn (array $zones): array => array_keys($zones));

    expect($values->sort()->values()->all())->toBe(collect(DateTimeZone::listIdentifiers())->sort()->values()->all());
});

test('zones are labelled readably with their current UTC offset', function (string $identifier, string $label) {
    expect(TimezoneOptions::options()[$identifier])->toBe($label);
})->with([
    'eastern daylight time' => ['America/New_York', 'New York (Eastern Time, UTC-4)'],
    'central' => ['America/Chicago', 'Chicago (Central Time, UTC-5)'],
    'half hour offset' => ['Asia/Kolkata', 'Kolkata (UTC+5:30)'],
    'underscores become spaces' => ['America/Los_Angeles', 'Los Angeles (Pacific Time, UTC-7)'],
    'utc itself' => ['UTC', 'UTC (UTC+0)'],
]);

test('the offset follows daylight saving time', function () {
    Date::setTestNow('2026-01-15 12:00');

    expect(TimezoneOptions::options()['America/New_York'])->toBe('New York (Eastern Time, UTC-5)');
});

test('zones are grouped by region with America first', function () {
    $groups = array_keys(TimezoneOptions::grouped());

    expect($groups[0])->toBe('America')
        ->and($groups)->toContain('Europe', 'Asia');
});

test('only real identifiers are valid', function (string $zone, bool $valid) {
    expect(TimezoneOptions::isValid($zone))->toBe($valid);
})->with([
    'new york' => ['America/New_York', true],
    'utc' => ['UTC', true],
    'made up' => ['Mars/Olympus_Mons', false],
    'empty' => ['', false],
    'wrong case' => ['america/new_york', false],
]);
