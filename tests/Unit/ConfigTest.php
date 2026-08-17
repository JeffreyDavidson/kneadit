<?php

use Illuminate\Support\Facades\Config;

test('kneadit plans config has required structure', function () {
    $plans = Config::array('kneadit.plans');

    expect($plans)->toBeArray()->not->toBeEmpty()->each->toHaveKeys(['name', 'description', 'founding_price_monthly']);

    foreach ($plans as $plan) {
        if (! is_array($plan)) {
            throw new RuntimeException('Each configured plan must be an array.');
        }

        expect($plan['name'])->toBeString()
            ->and($plan['founding_price_monthly'])->toBeNumeric();
    }
});

test('app config has required values', function () {
    expect(config('app.name'))->toBeString()->not->toBeEmpty()
        ->and(config('app.timezone'))->toBeString();
});
