<?php

use App\Events\Customers\CustomerBirthday;
use App\Models\Customers\Customer;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Event;
use JMac\Testing\Double;
use Stancl\Tenancy\Tenancy;

use function Pest\Laravel\artisan;

beforeEach(function () {
    setUpCentralTest();
});

test('each tenant picks birthdays by its own local date', function () {
    $timezones = [
        'new-york' => 'America/New_York',
        'sydney' => 'Australia/Sydney',
    ];
    foreach (array_keys($timezones) as $id) {
        createTenant(['id' => $id, 'email' => "{$id}@example.com"]);
    }

    // The real TenancyManager runs; only the database switch is replaced by
    // writing the entering tenant's timezone into the single test database.
    $tenancy = Double::for(Tenancy::class);
    $tenancy->allows('initialize')->resolves(function ($tenant) use ($timezones): void {
        settings([
            'birthday_program_enabled' => '1',
            'birthday_coupon_enabled' => '0',
            'timezone' => $timezones[$tenant->id],
        ]);
    });
    $tenancy->allows('end');
    app()->instance(Tenancy::class, $tenancy);

    Event::fake([CustomerBirthday::class]);
    // 16:00 on 5 Oct in New York, 07:00 on 6 Oct in Sydney.
    Date::setTestNow('2026-10-05 20:00');
    $newYorkCustomer = Customer::factory()->create(['email' => 'ny@example.com', 'birthday' => '1990-10-05']);
    $sydneyCustomer = Customer::factory()->create(['email' => 'sydney@example.com', 'birthday' => '1990-10-06']);

    artisan('birthday:send-emails', ['--force' => true])->assertSuccessful();

    Event::assertDispatchedTimes(CustomerBirthday::class, 2);
    Event::assertDispatched(CustomerBirthday::class, fn (CustomerBirthday $event): bool => $event->customer->is($newYorkCustomer));
    Event::assertDispatched(CustomerBirthday::class, fn (CustomerBirthday $event): bool => $event->customer->is($sydneyCustomer));
});
