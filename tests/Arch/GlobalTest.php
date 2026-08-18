<?php

declare(strict_types=1);

arch('no debugging statements in app code')
    ->expect(['dd', 'dump', 'ray', 'var_dump', 'print_r'])
    ->not->toBeUsedIn('App');

arch('no env calls outside config files')
    ->expect('env')
    ->not->toBeUsedIn('App');

test('no MySQL-only SQL functions in application code', function () {
    $mysqlFunctions = ['DATE_FORMAT', 'YEAR(', 'MONTH(', 'DAY(', 'FIELD(', 'IFNULL('];
    $violations = [];
    $appDir = __DIR__ . '/../../app';

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($appDir, FilesystemIterator::SKIP_DOTS),
    );

    foreach ($iterator as $file) {
        if ($file->getExtension() !== 'php') {
            continue;
        }

        $content = file_get_contents($file->getPathname());

        foreach ($mysqlFunctions as $fn) {
            if (str_contains($content, $fn)) {
                $relative = str_replace(realpath($appDir) . '/', '', $file->getPathname());
                $violations[] = "{$relative} uses MySQL-only function: {$fn}";
            }
        }
    }

    expect($violations)->toBeEmpty(
        "MySQL-only SQL found:\n" . implode("\n", $violations),
    );
});
