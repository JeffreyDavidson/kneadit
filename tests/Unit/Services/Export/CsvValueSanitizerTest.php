<?php

use App\Services\Export\CsvValueSanitizer;

test('prefixes spreadsheet formula starters in text values', function (string $value) {
    expect(CsvValueSanitizer::sanitize($value))->toBe("'{$value}");
})->with([
    'equals' => '=SUM(A1:A2)',
    'plus' => '+cmd',
    'minus' => '-cmd',
    'at' => '@cmd',
]);

test('preserves numeric values and ordinary text', function (mixed $value) {
    expect(CsvValueSanitizer::sanitize($value))->toBe($value);
})->with([
    'negative number' => '-12.50',
    'positive number' => '+12.50',
    'integer' => 42,
    'ordinary text' => 'Fresh bread',
    'null' => null,
]);

test('sanitizes every value in a row', function () {
    expect(CsvValueSanitizer::row(['=formula', 'safe', '-note']))
        ->toBe(["'=formula", 'safe', "'-note"]);
});
