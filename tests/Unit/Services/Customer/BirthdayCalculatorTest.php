<?php

use App\Services\Customer\BirthdayCalculator;
use Illuminate\Support\Facades\Date;

it('detects today is a birthday', function () {
    Date::setTestNow('2026-03-26');

    $calculator = new BirthdayCalculator;

    expect($calculator->isToday(Date::parse('1990-03-26')))->toBeTrue()->and($calculator->isToday(Date::parse('1990-04-15')))->toBeFalse()->and($calculator->isToday(null))->toBeFalse();
});
