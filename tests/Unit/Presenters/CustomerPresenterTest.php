<?php

use App\DataTransferObjects\Customers\CustomerMetrics;
use App\Models\Customers\Customer;
use App\Presenters\CustomerPresenter;
use App\Services\Customers\CustomerIntelligence;
use App\ValueObjects\Money;
use JMac\Testing\Double;

function makeCustomerMetrics(): CustomerMetrics
{
    return new CustomerMetrics(
        lifetimeValue: Money::fromDollars(150),
        orderCount: 3,
        averageOrderValue: Money::fromDollars(50),
        lastOrderDate: now()->subDays(2),
        daysSinceLastOrder: 2,
        isAtRisk: false,
        totalPoints: 120,
        lifetimePointsEarned: 200,
    );
}

test('delegates computed metrics to CustomerIntelligence', function () {
    $customer = new Customer(['name' => 'Ada']);
    $intelligence = Double::for(CustomerIntelligence::class);
    $intelligence->allows('metrics')->returns(makeCustomerMetrics());

    $presenter = new CustomerPresenter($customer, $intelligence);

    expect($presenter->lifetimeValue())->toBe(150.00)
        ->and($presenter->orderCount())->toBe(3)
        ->and($presenter->averageOrderValue())->toBe(50.00)
        ->and($presenter->totalPoints())->toBe(120)
        ->and($presenter->lifetimePointsEarned())->toBe(200)
        ->and($presenter->daysSinceLastOrder())->toBe(2)
        ->and($presenter->isAtRisk())->toBeFalse()
        ->and($presenter->lastOrderDate())->not->toBeNull();
});

test('memoizes metrics across multiple reads', function () {
    $customer = new Customer(['name' => 'Ada']);
    $intelligence = Double::for(CustomerIntelligence::class);
    $intelligence->allows('metrics')->returns(makeCustomerMetrics());

    $presenter = new CustomerPresenter($customer, $intelligence);

    $presenter->lifetimeValue();
    $presenter->orderCount();
    $presenter->totalPoints();
});

test('for() resolves CustomerIntelligence from the container', function () {
    $customer = new Customer(['name' => 'Linus']);
    $fakeMetrics = makeCustomerMetrics();
    $intelligence = Double::for(CustomerIntelligence::class);
    $intelligence->allows('metrics')->returns($fakeMetrics);
    app()->instance(CustomerIntelligence::class, $intelligence);

    $presenter = CustomerPresenter::for($customer);

    expect($presenter->orderCount())->toBe(3);
});

test('address() builds an Address value object from the customer columns', function () {
    $customer = new Customer([
        'address' => '123 Main St',
        'city' => 'Springfield',
        'state' => 'IL',
        'zip' => '62704',
    ]);

    $presenter = new CustomerPresenter($customer, Double::for(CustomerIntelligence::class));

    expect($presenter->address())
        ->street->toBe('123 Main St')
        ->city->toBe('Springfield')
        ->state->toBe('IL')
        ->zip->toBe('62704');
});
