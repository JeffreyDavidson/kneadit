<?php

declare(strict_types=1);

test('central route files do not depend on tenant presentation controllers', function () {
    $violations = [];
    $basePath = dirname(__DIR__, 2);

    foreach (glob($basePath.'/routes/central/*.php') ?: [] as $file) {
        $contents = file_get_contents($file);

        if ($contents === false) {
            throw new RuntimeException("Unable to read {$file}.");
        }

        if (preg_match_all('/^use App\\\\Http\\\\Controllers\\\\([^;]+);/m', $contents, $matches) !== false) {
            foreach ($matches[1] as $controller) {
                if (str_starts_with($controller, 'Tenant\\') || str_starts_with($controller, 'Storefront\\')) {
                    $violations[] = str_replace($basePath.'/', '', $file).': '.$controller;
                }
            }
        }
    }

    expect($violations)->toBeEmpty(
        "Central routes must not import tenant presentation controllers:\n".implode("\n", $violations),
    );
});

test('tenant route files keep tenant presentation controllers under tenant surfaces', function () {
    $violations = [];
    $basePath = dirname(__DIR__, 2);

    foreach (glob($basePath.'/routes/tenant/*.php') ?: [] as $file) {
        $contents = file_get_contents($file);

        if ($contents === false) {
            throw new RuntimeException("Unable to read {$file}.");
        }

        if (preg_match_all('/^use App\\\\Http\\\\Controllers\\\\([^;]+);/m', $contents, $matches) !== false) {
            foreach ($matches[1] as $controller) {
                if (str_starts_with($controller, 'Auth\\')
                    || str_starts_with($controller, 'Catering\\')
                    || str_starts_with($controller, 'Central\\InvoiceController')
                    || str_starts_with($controller, 'Central\\PrintProductLabelController')
                    || str_starts_with($controller, 'Order\\')) {
                    $violations[] = str_replace($basePath.'/', '', $file).': '.$controller;
                }
            }
        }
    }

    expect($violations)->toBeEmpty(
        "Tenant routes must use surface-aligned controller namespaces:\n".implode("\n", $violations),
    );
});

test('static route views resolve to existing Blade templates', function () {
    $missing = [];
    $basePath = dirname(__DIR__, 2);

    foreach (array_merge(
        glob($basePath.'/routes/*.php') ?: [],
        glob($basePath.'/routes/central/*.php') ?: [],
        glob($basePath.'/routes/tenant/*.php') ?: [],
    ) as $file) {
        $contents = file_get_contents($file);

        if ($contents === false) {
            throw new RuntimeException("Unable to read {$file}.");
        }

        preg_match_all(
            '/Route::view\(\s*[\'\"][^\'\"]+[\'\"]\s*,\s*[\'\"]([^\'\"]+)[\'\"]/',
            $contents,
            $matches,
        );

        foreach ($matches[1] as $view) {
            $viewPath = $basePath.'/resources/views/'.str_replace('.', '/', $view).'.blade.php';

            if (! is_file($viewPath)) {
                $missing[] = str_replace($basePath.'/', '', $file).': '.$view;
            }
        }
    }

    expect($missing)->toBeEmpty(
        "Static route views must resolve:\n".implode("\n", $missing),
    );
});
