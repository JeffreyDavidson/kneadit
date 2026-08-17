<?php

declare(strict_types=1);

test('Unit tests must not use RefreshDatabase', function () {
    $unitDir = dirname(__DIR__) . '/Unit';
    $violations = [];

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($unitDir, FilesystemIterator::SKIP_DOTS),
    );

    foreach ($iterator as $file) {
        if (! $file instanceof SplFileInfo) {
            continue;
        }

        if ($file->getExtension() !== 'php') {
            continue;
        }

        $contents = file_get_contents($file->getPathname());

        if ($contents === false) {
            throw new RuntimeException("Unable to read {$file->getPathname()}.");
        }

        if (str_contains($contents, 'Illuminate\Foundation\Testing\RefreshDatabase')) {
            $violations[] = str_replace(dirname(__DIR__) . '/', '', $file->getPathname());
        }
    }

    expect($violations)->toBeEmpty(
        "Unit tests must not touch the DB. Move these to tests/Integration/:\n" . implode("\n", $violations),
    );
});
