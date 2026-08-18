<?php

use App\Services\Content\QrCodeService;

test('generateSvg returns html string', function () {
    $svg = resolve(QrCodeService::class)->generateSvg('https://example.com', 200, '#000000');

    expect($svg)->toBeString()->toContain('svg');
});

test('generatePng returns binary string', function () {
    $png = resolve(QrCodeService::class)->generatePng('https://example.com', 200, '#000000');

    expect($png)->toBeString()->not->toBeEmpty();
});
