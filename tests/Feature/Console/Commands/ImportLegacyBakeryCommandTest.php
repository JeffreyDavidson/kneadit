<?php

use Illuminate\Support\Facades\Storage;

it('rejects a missing legacy export', function () {
    $this->artisan('tenant:import-legacy-bakery', ['tenant' => 'bakery-on-biscotto', 'file' => '/missing/export.json'])
        ->expectsOutput('The import file does not exist.')
        ->assertFailed();
});

it('summarizes a valid export without requiring a tenant during a dry run', function () {
    Storage::fake('local');
    $path = Storage::disk('local')->path('bakery-on-biscotto.json');
    file_put_contents($path, json_encode([
        'categories' => [['id' => 1, 'name' => 'Bread']],
        'products' => [['id' => 1, 'name' => 'Sourdough']],
    ], JSON_THROW_ON_ERROR));

    $this->artisan('tenant:import-legacy-bakery', [
        'tenant' => 'bakery-on-biscotto',
        'file' => $path,
        '--dry-run' => true,
    ])->assertSuccessful();
});

it('rejects a valid JSON document with an invalid dataset shape', function () {
    Storage::fake('local');
    $path = Storage::disk('local')->path('invalid-dataset.json');
    file_put_contents($path, json_encode(['products' => 'not-a-record-list'], JSON_THROW_ON_ERROR));

    $this->artisan('tenant:import-legacy-bakery', [
        'tenant' => 'bakery-on-biscotto',
        'file' => $path,
        '--dry-run' => true,
    ])->expectsOutput('The import file must contain an object of dataset arrays.')
        ->assertFailed();
});
