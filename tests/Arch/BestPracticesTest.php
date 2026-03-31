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

test('model static calls in controllers must use explicit query()', function () {
    $controllerFiles = collect(glob(__DIR__ . '/../../app/Http/Controllers/**/*.php'))
        ->merge(glob(__DIR__ . '/../../app/Http/Controllers/*.php'))
        ->reject(fn ($file) => str_ends_with($file, 'Controller.php') && basename($file) === 'Controller.php');

    $modelClasses = collect(glob(__DIR__ . '/../../app/Models/*.php'))
        ->map(fn ($file) => pathinfo($file, PATHINFO_FILENAME))
        ->all();

    $violations = [];

    foreach ($controllerFiles as $file) {
        $contents = file_get_contents($file);
        $shortName = basename($file);

        foreach ($modelClasses as $model) {
            // Match Model::anything() that is NOT ::query(), ::class, ::factory(), ::dispatch(), ::find(, ::findOrFail(
            if (preg_match_all("/\\b{$model}::(?!query|class|factory|dispatch|find)(\w+)\s*\(/", $contents, $matches)) {
                foreach ($matches[1] as $method) {
                    $violations[] = "{$shortName}: {$model}::{$method}() should be {$model}::query()->{$method}()";
                }
            }
        }
    }

    expect($violations)->toBeEmpty(
        "Controllers must use Model::query()->method() instead of Model::method():\n" . implode("\n", $violations),
    );
});

arch('controllers should not use Mail facade directly')
    ->expect('Illuminate\Support\Facades\Mail')
    ->not->toBeUsedIn('App\Http\Controllers');

arch('all listeners should implement ShouldQueue')
    ->expect('App\Listeners')
    ->toImplement('Illuminate\Contracts\Queue\ShouldQueue');

test('all listeners have retry configuration', function () {
    $listenerFiles = glob(__DIR__ . '/../../app/Listeners/*.php');
    $violations = [];

    foreach ($listenerFiles as $file) {
        $contents = file_get_contents($file);
        $name = basename($file, '.php');

        if (! str_contains($contents, '$tries')) {
            $violations[] = "{$name}: missing \$tries property";
        }
        if (! str_contains($contents, '$backoff')) {
            $violations[] = "{$name}: missing \$backoff property";
        }
    }

    expect($violations)->toBeEmpty(
        "Listeners must have retry configuration:\n" . implode("\n", $violations),
    );
});

test('all models have factories', function () {
    $modelFiles = collect(glob(__DIR__ . '/../../app/Models/*.php'))
        ->map(fn ($file) => pathinfo($file, PATHINFO_FILENAME))
        ->reject(fn ($name) => in_array($name, ['ImpersonationToken', 'Tenant']))
        ->values();

    $factoryFiles = collect(glob(__DIR__ . '/../../database/factories/*.php'))
        ->map(fn ($file) => str_replace('Factory', '', pathinfo($file, PATHINFO_FILENAME)))
        ->values()
        ->all();

    $missing = $modelFiles->reject(fn ($model) => in_array($model, $factoryFiles))->all();

    expect($missing)->toBeEmpty(
        'Models missing factories: ' . implode(', ', $missing),
    );
});
