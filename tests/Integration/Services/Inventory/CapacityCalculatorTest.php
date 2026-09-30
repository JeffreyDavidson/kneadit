<?php

use App\Enums\Staff\DayOfWeek;
use App\Models\Operations\BusinessSchedule;
use App\Models\Operations\CapacityLimit;
use App\Models\Operations\Holiday;
use App\Services\Inventory\CapacityCalculator;
use Illuminate\Support\Facades\Date;

beforeEach(fn () => setUpTenantTest());

it('returns default capacity when no limit exists for a date', function () {
    settings(['default_daily_capacity' => 25]);

    $calculator = resolve(CapacityCalculator::class);
    $max = $calculator->getMaxOrders('2026-04-01');

    expect($max)->toBe(25);
});

it('applies a weekday limit to every matching weekday', function () {
    settings(['default_daily_capacity' => 25]);
    CapacityLimit::factory()->weekday(DayOfWeek::Monday)->create(['max_orders' => 8]);

    $calculator = resolve(CapacityCalculator::class);

    expect($calculator->getMaxOrders('2026-10-05'))->toBe(8)
        ->and($calculator->getMaxOrders('2026-10-12'))->toBe(8)
        ->and($calculator->getMaxOrders('2026-10-06'))->toBe(25);
});

it('prefers a specific-date limit over the weekday limit', function () {
    CapacityLimit::factory()->weekday(DayOfWeek::Monday)->create(['max_orders' => 8]);
    CapacityLimit::factory()->specificDate('2026-10-12')->create(['max_orders' => 3]);

    $calculator = resolve(CapacityCalculator::class);

    expect($calculator->getMaxOrders('2026-10-12'))->toBe(3)
        ->and($calculator->getMaxOrders('2026-10-05'))->toBe(8);
});

it('treats a blocked day as full', function () {
    settings(['default_daily_capacity' => 25]);
    CapacityLimit::factory()->weekday(DayOfWeek::Monday)->blocked()->create();

    $calculator = resolve(CapacityCalculator::class);

    expect($calculator->isAvailable('2026-10-05'))->toBeFalse()
        ->and($calculator->remainingSlots('2026-10-05'))->toBe(0);
});

it('uses the most specific order limit for a date', function (array $levels, int $expected) {
    settings(['default_daily_capacity' => 25]);
    $create = [
        'date limit' => fn (array $overrides) => CapacityLimit::factory()->specificDate('2026-10-05')->open()->create(['max_orders' => 3, ...$overrides]),
        'holiday' => fn (array $overrides) => Holiday::factory()->active()->create(['date' => '2026-10-05', 'max_orders' => 4, ...$overrides]),
        'weekday limit' => fn (array $overrides) => CapacityLimit::factory()->weekday(DayOfWeek::Monday)->open()->create(['max_orders' => 5, ...$overrides]),
        'schedule' => fn (array $overrides) => BusinessSchedule::factory()->open()->create(['day_of_week' => 1, 'max_orders' => 6, ...$overrides]),
    ];
    foreach ($levels as $level => $overrides) {
        $create[$level]($overrides);
    }

    $max = resolve(CapacityCalculator::class)->getMaxOrders('2026-10-05');

    expect($max)->toBe($expected);
})->with([
    'a date limit beats everything' => [['date limit' => [], 'holiday' => [], 'weekday limit' => [], 'schedule' => []], 3],
    'a holiday beats weekly limits' => [['holiday' => [], 'weekday limit' => [], 'schedule' => []], 4],
    'a weekday limit beats the schedule' => [['weekday limit' => [], 'schedule' => []], 5],
    'the schedule beats the default' => [['schedule' => []], 6],
    'the default applies when nothing is set' => [[], 25],
    'a date limit of 0 falls through' => [['date limit' => ['max_orders' => 0], 'holiday' => []], 4],
    'a holiday without a max falls through' => [['holiday' => ['max_orders' => null], 'weekday limit' => []], 5],
    'an inactive holiday is ignored' => [['holiday' => ['is_active' => false], 'schedule' => []], 6],
    'a schedule without a max falls through' => [['schedule' => ['max_orders' => null]], 25],
    'a blocked date limit beats everything' => [['date limit' => ['is_blocked' => true], 'holiday' => [], 'schedule' => []], 0],
    'a blocked weekday limit beats the schedule' => [['weekday limit' => ['is_blocked' => true], 'schedule' => []], 0],
    'a holiday overrides a blocked weekday limit' => [['holiday' => [], 'weekday limit' => ['is_blocked' => true]], 4],
]);

it('is unavailable once a holiday order deadline has passed', function () {
    Date::setTestNow('2026-12-21 09:00');
    Holiday::factory()->active()->create(['date' => '2026-12-25', 'order_deadline' => '2026-12-20']);

    $available = resolve(CapacityCalculator::class)->isAvailable('2026-12-25');

    expect($available)->toBeFalse();
});
