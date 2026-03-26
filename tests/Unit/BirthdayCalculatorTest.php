<?php

use App\Services\BirthdayCalculator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Date;

it('detects today is a birthday', function () {
    Date::setTestNow('2026-03-26');

    $calculator = new BirthdayCalculator;

    expect($calculator->isToday(Carbon::parse('1990-03-26')))->toBeTrue();
    expect($calculator->isToday(Carbon::parse('1990-04-15')))->toBeFalse();
    expect($calculator->isToday(null))->toBeFalse();
});
