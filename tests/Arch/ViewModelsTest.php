<?php

declare(strict_types=1);

arch('view models must not use the DB facade')
    ->expect('Illuminate\Support\Facades\DB')
    ->not->toBeUsedIn('App\ViewModels');

test('view models must not call Model::query() statically', function () {
    $viewModelsDir = dirname(__DIR__, 2) . '/app/ViewModels';
    $modelClasses = collect(
        new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(dirname(__DIR__, 2) . '/app/Models', FilesystemIterator::SKIP_DOTS),
        ),
    )
        ->filter(fn ($file) => $file->getExtension() === 'php')
        ->reject(fn ($file) => str_contains($file->getPathname(), DIRECTORY_SEPARATOR . 'Concerns' . DIRECTORY_SEPARATOR))
        ->map(fn ($file) => $file->getBasename('.php'))
        ->values()
        ->all();

    $violations = [];

    if (! is_dir($viewModelsDir)) {
        expect(true)->toBeTrue();

        return;
    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($viewModelsDir, FilesystemIterator::SKIP_DOTS),
    );

    foreach ($iterator as $file) {
        if ($file->getExtension() !== 'php') {
            continue;
        }

        $contents = file_get_contents($file->getPathname());
        $relative = str_replace(dirname(__DIR__, 2) . '/', '', $file->getPathname());

        foreach ($modelClasses as $model) {
            if (preg_match("/\\b{$model}::query\\s*\\(/", $contents)) {
                $violations[] = "{$relative}: calls {$model}::query() (controllers should fetch and pass results in)";
            }
        }
    }

    expect($violations)->toBeEmpty(
        "ViewModels must not query the database directly:\n" . implode("\n", $violations),
    );
});
