<?php

use App\Enums\Staff\DayOfWeek;

test('options returns all days as key value pairs', function () {
    $options = DayOfWeek::options();

    expect($options)->toHaveCount(7)
        ->and($options['monday'])->toBe('Monday')
        ->and($options['sunday'])->toBe('Sunday');
});
