<?php

declare(strict_types=1);

arch('enums should be string backed')
    ->expect('App\Enums')
    ->toBeEnums()
    ->toBeStringBackedEnums();

arch('models should extend eloquent model')
    ->expect('App\Models')
    ->toExtend('Illuminate\Database\Eloquent\Model')
    ->ignoring('App\Models\Concerns');

test('models must not declare resolveRouteBinding or getRouteKeyName', function () {
    $modelsDir = dirname(__DIR__, 2) . '/app/Models';
    $violations = [];

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($modelsDir, FilesystemIterator::SKIP_DOTS),
    );

    foreach ($iterator as $file) {
        if ($file->getExtension() !== 'php') {
            continue;
        }

        $contents = file_get_contents($file->getPathname());
        $relative = str_replace(dirname(__DIR__, 2) . '/', '', $file->getPathname());

        if (preg_match('/function\s+resolveRouteBinding\s*\(/', $contents)) {
            $violations[] = "{$relative}: declares resolveRouteBinding (extract to app/Routing/Resolvers/)";
        }
        if (preg_match('/function\s+getRouteKeyName\s*\(/', $contents)) {
            $violations[] = "{$relative}: declares getRouteKeyName (use {model:column} in route definitions)";
        }
    }

    expect($violations)->toBeEmpty(
        "Routing concerns must live in the routing layer, not on models:\n" . implode("\n", $violations),
    );
});
