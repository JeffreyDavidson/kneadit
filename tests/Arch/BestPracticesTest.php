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

test('view calls with 3+ array items should be multiline', function () {
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
        $relative = str_replace(dirname(__DIR__, 2) . '/app' . DIRECTORY_SEPARATOR, '', $file->getPathname());

        // Match single-line arrays with 3+ => pairs: ['a' => $a, 'b' => $b, 'c' => $c]
        if (preg_match_all('/\[([^\[\]]*=>.*=>.*=>.*)\]/', $content, $matches, PREG_OFFSET_CAPTURE)) {
            foreach ($matches[0] as $match) {
                $line = substr_count(substr($content, 0, $match[1]), "\n") + 1;
                $arrowCount = substr_count($match[0], '=>');
                if ($arrowCount >= 3) {
                    $violations[] = "{$relative}:{$line} — single-line array with {$arrowCount} items";
                }
            }
        }
    }

    expect($violations)->toBeEmpty(
        "Arrays with 3+ items should be multiline:\n" . implode("\n", $violations),
    );
});
