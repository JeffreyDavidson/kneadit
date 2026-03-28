<?php

test('kneadit plans config has required structure', function () {
    $plans = config('kneadit.plans');

    expect($plans)->toBeArray()->not->toBeEmpty();

    foreach ($plans as $key => $plan) {
        expect($plan)->toHaveKeys(['name', 'description', 'founding_price_monthly'])
            ->and($plan['name'])->toBeString()
            ->and($plan['founding_price_monthly'])->toBeNumeric();
    }
});

test('app config has required values', function () {
    expect(config('app.name'))->toBeString()->not->toBeEmpty()
        ->and(config('app.timezone'))->toBeString();
});
