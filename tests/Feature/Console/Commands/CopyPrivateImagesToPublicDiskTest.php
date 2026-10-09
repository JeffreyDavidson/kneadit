<?php

beforeEach(fn () => setUpCentralTest());

test('images:copy-private-to-public is a dry run by default and succeeds with no tenants', function () {
    $this->artisan('images:copy-private-to-public')
        ->expectsOutputToContain('Dry run: 0 files would be copied')
        ->assertSuccessful();
});

test('images:copy-private-to-public --apply reports the copied total', function () {
    $this->artisan('images:copy-private-to-public', ['--apply' => true])
        ->expectsOutputToContain('Copied 0 files to the public disk.')
        ->assertSuccessful();
});
