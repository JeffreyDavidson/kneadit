<?php

use App\Filament\Widgets\BirthdayWidget;
use App\Models\Customers\Customer;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    setUpTenantTest();
    test()->widget = new BirthdayWidget;
});

test('get upcoming birthdays returns empty when no customers', function () {
    expect(test()->widget->getUpcomingBirthdays())->toBeEmpty();
});

test('get upcoming birthdays returns empty when no birthdays set', function () {
    Customer::factory()->create(['birthday' => null]);

    expect(test()->widget->getUpcomingBirthdays())->toBeEmpty();
});

test('get upcoming birthdays includes customer with upcoming birthday', function () {
    // Birthday in 5 days (same month-day, this year)
    $birthdayDate = now()->addDays(5);
    Customer::factory()->create([
        'name' => 'Birthday Customer',
        'birthday' => $birthdayDate->subYears(25),
    ]);

    $birthdays = test()->widget->getUpcomingBirthdays();

    expect($birthdays)->toHaveCount(1)
        ->and($birthdays->first()->customer_name)->toBe('Birthday Customer');
});

test('get upcoming birthdays selects only fields used for the widget entries', function () {
    Customer::factory()->create([
        'name' => 'Birthday Customer',
        'birthday' => now()->addDays(5)->subYears(25),
    ]);
    $customerQueries = [];

    DB::listen(function (QueryExecuted $query) use (&$customerQueries): void {
        $sql = strtolower(str_replace(['"', '`', '[', ']'], '', $query->sql));

        if (str_contains($sql, 'from customers')) {
            $customerQueries[] = $sql;
        }
    });

    $birthdays = test()->widget->getUpcomingBirthdays();

    expect($birthdays->first()->customer_name)->toBe('Birthday Customer')
        ->and($customerQueries)->toHaveCount(1);

    $selectClause = explode(' from customers', $customerQueries[0], 2)[0];

    expect($selectClause)->toBe('select name, birthday');
});

test('get upcoming birthdays excludes customers beyond 30 days', function () {
    $birthdayDate = now()->addDays(35);
    Customer::factory()->create([
        'birthday' => $birthdayDate->subYears(30),
    ]);

    $birthdays = test()->widget->getUpcomingBirthdays();

    expect($birthdays)->toBeEmpty();
});

test('get upcoming birthdays limited to 5 results at medium size', function () {
    test()->widget->dashboardSize = 'md';

    for ($i = 1; $i <= 8; $i++) {
        Customer::factory()->create([
            'birthday' => now()->addDays($i)->subYears(25),
        ]);
    }

    $birthdays = test()->widget->getUpcomingBirthdays();

    expect($birthdays)->toHaveCount(5);
});

test('get upcoming birthdays sorted by days until', function () {
    Customer::factory()->create([
        'name' => 'Later',
        'birthday' => now()->addDays(20)->subYears(25),
    ]);
    Customer::factory()->create([
        'name' => 'Sooner',
        'birthday' => now()->addDays(2)->subYears(25),
    ]);

    $birthdays = test()->widget->getUpcomingBirthdays();

    expect($birthdays)->toHaveCount(2)
        ->and($birthdays->first()->customer_name)->toBe('Sooner')
        ->and($birthdays->last()->customer_name)->toBe('Later');
});
