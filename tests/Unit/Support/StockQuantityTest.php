<?php

use App\Support\StockQuantity;

test('display shows 2 decimals', function (float|string|null $value, string $expected) {
    expect(StockQuantity::display($value))->toBe($expected);
})->with([
    'whole number' => [8, '8.00'],
    'a stored decimal string' => ['9.9970', '10.00'],
    'more than 2 decimals' => [1.2345, '1.23'],
    'zero' => [0, '0.00'],
    'null' => [null, '0.00'],
    'a negative number' => [-2.5, '-2.50'],
]);

test('display keeps up to 4 decimals when 2 would show a non-zero value as 0', function (float|string $value, string $expected) {
    expect(StockQuantity::display($value))->toBe($expected);
})->with([
    'a thousandth' => [0.003, '0.003'],
    'a ten thousandth' => ['0.0001', '0.0001'],
    'trailing zeros trimmed' => ['0.0040', '0.004'],
    'a negative thousandth' => [-0.003, '-0.003'],
]);

test('input shows at least 2 decimals and never drops a stored digit', function (float|string|null $value, string $expected) {
    expect(StockQuantity::input($value))->toBe($expected);
})->with([
    'whole number' => ['8.0000', '8.00'],
    'tenths' => ['0.5000', '0.50'],
    'thousandths' => ['9.9970', '9.997'],
    'four decimals' => ['1.2345', '1.2345'],
    'null' => [null, '0.00'],
]);
