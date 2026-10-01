<?php

declare(strict_types=1);

use App\Services\Settings\TenantSettings;

test('views only read properties that exist on TenantSettings', function () {
    $properties = array_map(
        fn (ReflectionProperty $property): string => $property->getName(),
        new ReflectionClass(TenantSettings::class)->getProperties(),
    );
    $violations = [];

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator(dirname(__DIR__, 2).'/resources/views', FilesystemIterator::SKIP_DOTS),
    );

    foreach ($iterator as $file) {
        if (! $file instanceof SplFileInfo || ! str_ends_with($file->getFilename(), '.blade.php')) {
            continue;
        }

        $contents = file_get_contents($file->getPathname());

        if ($contents === false) {
            throw new RuntimeException("Unable to read {$file->getPathname()}.");
        }

        preg_match_all('/TenantSettings::class\)\??->([A-Za-z_]\w*)/', $contents, $matches);

        foreach ($matches[1] as $property) {
            if (! in_array($property, $properties, true)) {
                $violations[] = "{$file->getFilename()}: ->{$property}";
            }
        }
    }

    expect($violations)->toBeEmpty(
        "These views read properties TenantSettings doesn't have:\n".implode("\n", $violations),
    );
});
