<?php

declare(strict_types=1);

test('central delivery and read layers do not open tenant context directly', function () {
    $violations = [];
    foreach (['app/Http/Controllers', 'app/Queries', 'app/Reports'] as $directory) {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(dirname(__DIR__, 2) . '/' . $directory, FilesystemIterator::SKIP_DOTS),
        );

        foreach ($iterator as $file) {
            if (! $file instanceof SplFileInfo || $file->getExtension() !== 'php') {
                continue;
            }

            $contents = file_get_contents($file->getPathname());
            if ($contents !== false && preg_match('/(?:DB::connection|connection)\(\s*[\'\"]tenant|tenancy\(\)->(?:initialize|run)\s*\(/', $contents) === 1) {
                $violations[] = $file->getPathname();
            }
        }
    }

    expect($violations)->toBeEmpty();
});

arch('reports stay independent of delivery adapters')
    ->expect(['App\Http', 'App\Filament', 'App\Console'])
    ->not->toBeUsedIn('App\Reports');

arch('domain actions stay independent of delivery adapters')
    ->expect(['App\Http', 'App\Filament', 'App\Console'])
    ->not->toBeUsedIn('App\Actions');

arch('domain services stay independent of console commands')
    ->expect('App\Console')
    ->not->toBeUsedIn('App\Services');
