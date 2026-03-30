<?php

declare(strict_types=1);

arch('actions should be invokable')
    ->expect('App\Actions')
    ->toHaveMethod('__invoke');

arch('form requests should extend FormRequest')
    ->expect('App\Http\Requests')
    ->toExtend('Illuminate\Foundation\Http\FormRequest');

arch('services should be classes')
    ->expect('App\Services')
    ->toBeClasses();

arch('observers should end with Observer')
    ->expect('App\Observers')
    ->toHaveSuffix('Observer');

arch('enums should be string-backed')
    ->expect('App\Enums')
    ->toBeStringBackedEnums();

arch('controllers should not use env() directly')
    ->expect('env')
    ->not->toBeUsedIn('App\Http\Controllers');

arch('models should not use DB facade')
    ->expect('Illuminate\Support\Facades\DB')
    ->not->toBeUsedIn('App\Models');

arch('exceptions should be classes')
    ->expect('App\Exceptions')
    ->toBeClasses();

arch('mailables should extend BaseMailable')
    ->expect('App\Mail')
    ->toExtend('App\Mail\BaseMailable')
    ->ignoring(['App\Mail\BaseMailable', 'App\Mail\Concerns']);

arch('controllers should not use compact() for view data')
    ->expect('compact')
    ->not->toBeUsedIn('App\Http\Controllers');

test('associative arrays in controllers should be multiline', function () {
    $violations = [];
    $controllersDir = dirname(__DIR__, 2) . '/app/Http/Controllers';

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($controllersDir, FilesystemIterator::SKIP_DOTS),
    );

    foreach ($iterator as $file) {
        if ($file->getExtension() !== 'php') {
            continue;
        }

        $content = file_get_contents($file->getPathname());
        $lines = explode("\n", $content);
        $relative = str_replace(dirname(__DIR__, 2) . '/app' . DIRECTORY_SEPARATOR, '', $file->getPathname());

        foreach ($lines as $lineNum => $sourceLine) {
            // Skip lines without single-line associative arrays
            if (! preg_match('/\[([^\[\]\n]*=>[^\[\]\n]*)\]/', $sourceLine)) {
                continue;
            }

            // Skip closure arrays (eager loading with fn)
            if (str_contains($sourceLine, 'fn(') || str_contains($sourceLine, 'fn (')) {
                continue;
            }

            // Skip eager loading ->with([...])
            if (str_contains($sourceLine, '->with(')) {
                continue;
            }

            // Skip withErrors([...])
            if (str_contains($sourceLine, 'withErrors(')) {
                continue;
            }

            // Skip lines that are nested array items (indented, not a statement)
            $trimmed = ltrim($sourceLine);
            if (str_starts_with($trimmed, '[') && ! str_starts_with($trimmed, '[\'') === false) {
                if (! str_contains($sourceLine, 'return') && ! str_contains($sourceLine, 'view(') && ! str_contains($sourceLine, 'json(')) {
                    continue;
                }
            }

            $violations[] = $relative . ':' . ($lineNum + 1) . ' — single-line associative array';
        }
    }

    expect($violations)->toBeEmpty(
        "Associative arrays should be multiline:\n" . implode("\n", array_slice($violations, 0, 30)),
    );
});
