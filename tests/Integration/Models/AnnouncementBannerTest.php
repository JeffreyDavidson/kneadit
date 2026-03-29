<?php

beforeEach(function () {
    setUpTenantTest();
});

test('announcement settings can be stored', function () {
    settings(['announcement_enabled' => '1']);
    settings(['announcement_text' => 'We are closed for the holiday!']);
    settings(['announcement_type' => 'warning']);

    expect(settings('announcement_enabled'))->toBe('1')->and(settings('announcement_text'))->toBe('We are closed for the holiday!')->and(settings('announcement_type'))->toBe('warning');
});

test('announcement defaults to disabled', function () {
    expect(settings('announcement_enabled', '0'))->toBe('0');
});

test('announcement type defaults to info', function () {
    expect(settings('announcement_type', 'info'))->toBe('info');
});

test('announcement text defaults to empty', function () {
    expect(settings('announcement_text', ''))->toBeEmpty();
});
