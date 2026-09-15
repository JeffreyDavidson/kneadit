<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use RectorLaravel\Rector\PropertyFetch\ReplaceFakerPropertyFetchWithMethodCallRector;
use RectorLaravel\Set\LaravelSetProvider;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/app',
        __DIR__ . '/bootstrap/app.php',
        __DIR__ . '/bootstrap/providers.php',
        __DIR__ . '/config',
        __DIR__ . '/database/factories',
        __DIR__ . '/database/seeders',
        __DIR__ . '/routes',
    ])
    ->withSkip([
        // Keep fixture generation readable until the Faker property migration is intentional.
        ReplaceFakerPropertyFetchWithMethodCallRector::class,
    ])
    ->withSetProviders(LaravelSetProvider::class)
    ->withComposerBased(laravel: true);
