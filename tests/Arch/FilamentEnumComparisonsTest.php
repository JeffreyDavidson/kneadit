<?php

declare(strict_types=1);

/**
 * A Select built with ->options(SomeEnum::class) keeps the enum instance as its state,
 * so `$get('field') === SomeEnum::Case->value` is always false. Compare enums with
 * `$get->enum('field', SomeEnum::class)` instead.
 */
const FILAMENT_ENUM_COMPARISON_PATTERN = '/\$get\(\s*[\'"][^\'"]+[\'"]\s*\)\s*[!=]==\s*\\\\?[\w\\\\]+::\w+->value\b/';

test('Filament code does not compare $get() state to an enum value', function () {
    $rootDir = dirname(__DIR__, 2);
    $violations = [];

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator("{$rootDir}/app/Filament", FilesystemIterator::SKIP_DOTS),
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

        if (preg_match(FILAMENT_ENUM_COMPARISON_PATTERN, $contents) === 1) {
            $violations[] = str_replace("{$rootDir}/", '', $file->getPathname());
        }
    }

    expect($violations)->toBeEmpty(
        "Compare enum state with \$get->enum('field', EnumClass::class) in:\n".implode("\n", $violations),
    );
});

test('the enum comparison pattern is detected', function (string $code) {
    expect($code)->toMatch(FILAMENT_ENUM_COMPARISON_PATTERN);
})->with([
    'strict equals' => ['$get(\'type\') === CouponType::Fixed->value'],
    'strict not equals' => ['$get("type") !== \\App\\Enums\\CouponType::Fixed->value'],
]);
