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
    ->toBeClasses()
    ->ignoring('App\Services\Platform\HealthChecks\Contracts');

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
    ->toExtend(App\Mail\BaseMailable::class)
    ->ignoring([App\Mail\BaseMailable::class, 'App\Mail\Concerns']);

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
        if (! $file instanceof SplFileInfo) {
            continue;
        }

        if ($file->getExtension() !== 'php') {
            continue;
        }

        $content = file_get_contents($file->getPathname());

        if ($content === false) {
            throw new RuntimeException("Unable to read {$file->getPathname()}.");
        }

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
    $controllerFiles = collect(glob(__DIR__ . '/../../app/Http/Controllers/**/*.php') ?: [])
        ->merge(glob(__DIR__ . '/../../app/Http/Controllers/*.php') ?: [])
        ->reject(fn ($file) => str_ends_with($file, 'Controller.php') && basename($file) === 'Controller.php');

    $modelClasses = [];
    $modelIterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator(__DIR__ . '/../../app/Models', FilesystemIterator::SKIP_DOTS),
    );

    foreach ($modelIterator as $file) {
        if (! $file instanceof SplFileInfo || $file->getExtension() !== 'php') {
            continue;
        }

        if (str_contains($file->getPathname(), DIRECTORY_SEPARATOR . 'Concerns' . DIRECTORY_SEPARATOR)) {
            continue;
        }

        $modelClasses[] = $file->getBasename('.php');
    }

    $violations = [];

    foreach ($controllerFiles as $file) {
        $contents = file_get_contents($file);

        if ($contents === false) {
            throw new RuntimeException("Unable to read {$file}.");
        }

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

arch('mail classes should not call settings() directly')
    ->expect('settings')
    ->not->toBeUsedIn('App\Mail')
    ->ignoring('App\Mail\Concerns');

arch('all listeners should extend QueuedListener')
    ->expect('App\Listeners')
    ->toExtend(App\Listeners\QueuedListener::class)
    ->ignoring([
        App\Listeners\QueuedListener::class,
        // Scheduler lifecycle events contain process-local task objects and
        // must be recorded synchronously rather than serialized to a queue.
        App\Listeners\Platform\RecordScheduledTaskStatusListener::class,
    ]);

test('all listeners have retry configuration', function () {
    $listenerFiles = glob(__DIR__ . '/../../app/Listeners/*.php') ?: [];
    $violations = [];

    foreach ($listenerFiles as $file) {
        $contents = file_get_contents($file);

        if ($contents === false) {
            throw new RuntimeException("Unable to read {$file}.");
        }

        $name = basename($file, '.php');

        if ($name === 'QueuedListener') {
            continue;
        }

        $extendsBase = str_contains($contents, 'extends QueuedListener');

        if (! $extendsBase && ! str_contains($contents, '$tries')) {
            $violations[] = "{$name}: missing \$tries property (extend QueuedListener or define directly)";
        }
        if (! $extendsBase && ! str_contains($contents, '$backoff')) {
            $violations[] = "{$name}: missing \$backoff property (extend QueuedListener or define directly)";
        }
    }

    expect($violations)->toBeEmpty(
        "Listeners must have retry configuration:\n" . implode("\n", $violations),
    );
});

test('all models have factories', function () {
    $modelFiles = collect(iterator_to_array(new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator(__DIR__ . '/../../app/Models', FilesystemIterator::SKIP_DOTS),
    )))
        ->filter(fn (mixed $file): bool => $file instanceof SplFileInfo && $file->getExtension() === 'php')
        ->reject(fn (SplFileInfo $file): bool => str_contains($file->getPathname(), DIRECTORY_SEPARATOR . 'Concerns' . DIRECTORY_SEPARATOR))
        ->map(fn (SplFileInfo $file): string => $file->getBasename('.php'))
        ->reject(fn ($name) => in_array($name, ['ImpersonationToken', 'Tenant']))
        ->values();

    $factoryFiles = collect(iterator_to_array(new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator(__DIR__ . '/../../database/factories', FilesystemIterator::SKIP_DOTS),
    )))
        ->filter(fn (mixed $file): bool => $file instanceof SplFileInfo && $file->getExtension() === 'php')
        ->map(fn (SplFileInfo $file): string => str_replace('Factory', '', $file->getBasename('.php')))
        ->values()
        ->all();

    $missing = $modelFiles->reject(fn ($model) => in_array($model, $factoryFiles))->all();

    expect($missing)->toBeEmpty(
        'Models missing factories: ' . implode(', ', $missing),
    );
});
