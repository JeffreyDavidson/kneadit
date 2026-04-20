<?php

use App\DataTransferObjects\Customers\CustomerMetrics;
use App\Models\Customers\Customer;
use App\Presenters\CustomerPresenter;
use App\Services\Customers\CustomerIntelligence;

function makeCustomerMetrics(): CustomerMetrics
{
    return new CustomerMetrics(
        lifetimeValue: 150.00,
        orderCount: 3,
        averageOrderValue: 50.00,
        lastOrderDate: now()->subDays(2),
        daysSinceLastOrder: 2,
        isAtRisk: false,
        totalPoints: 120,
        lifetimePointsEarned: 200,
    );
}

test('delegates computed metrics to CustomerIntelligence', function () {
    $customer = new Customer(['name' => 'Ada']);
    $intelligence = Mockery::mock(CustomerIntelligence::class);
    $intelligence->shouldReceive('metrics')->once()->with($customer)->andReturn(makeCustomerMetrics());

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
    $intelligence = Mockery::mock(CustomerIntelligence::class);
    $intelligence->shouldReceive('metrics')->once()->andReturn(makeCustomerMetrics());

    $presenter = new CustomerPresenter($customer, $intelligence);

    $presenter->lifetimeValue();
    $presenter->orderCount();
    $presenter->totalPoints();
});

test('for() resolves CustomerIntelligence from the container', function () {
    $customer = new Customer(['name' => 'Linus']);
    $fakeMetrics = makeCustomerMetrics();
    app()->instance(CustomerIntelligence::class, Mockery::mock(CustomerIntelligence::class, function ($mock) use ($fakeMetrics) {
        $mock->shouldReceive('metrics')->once()->andReturn($fakeMetrics);
    }));

    $presenter = CustomerPresenter::for($customer);

    expect($presenter->orderCount())->toBe(3);
});
