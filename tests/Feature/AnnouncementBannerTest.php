<?php

use App\Models\Setting;

beforeEach(function () {
    setUpTenantTest();
});

test('announcement settings can be stored', function () {
    Setting::set('announcement_enabled', '1');
    Setting::set('announcement_text', 'We are closed for the holiday!');
    Setting::set('announcement_type', 'warning');

    expect(Setting::get('announcement_enabled'))->toBe('1');
    expect(Setting::get('announcement_text'))->toBe('We are closed for the holiday!');
    expect(Setting::get('announcement_type'))->toBe('warning');
});

test('announcement defaults to disabled', function () {
    expect(Setting::get('announcement_enabled', '0'))->toBe('0');
});

test('announcement type defaults to info', function () {
    expect(Setting::get('announcement_type', 'info'))->toBe('info');
});

test('announcement text defaults to empty', function () {
    expect(Setting::get('announcement_text', ''))->toBe('');
});
