<?php

namespace App\Services\Inventory;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductCsvUploadResolver
{
    private const DIRECTORY = 'csv-imports';

    public function resolve(string $path): ?UploadedFile
    {
        if (! $this->isExpectedPath($path)) {
            return null;
        }

        $disk = Storage::disk('local');
        $importRoot = realpath($disk->path(self::DIRECTORY));
        $resolvedPath = realpath($disk->path($path));

        if ($importRoot === false || $resolvedPath === false) {
            return null;
        }

        if (! Str::startsWith($resolvedPath, $importRoot . DIRECTORY_SEPARATOR) || ! is_file($resolvedPath)) {
            return null;
        }

        return new UploadedFile(
            $resolvedPath,
            basename($resolvedPath),
            'text/csv',
            test: true,
        );
    }

    private function isExpectedPath(string $path): bool
    {
        if (! Str::startsWith($path, self::DIRECTORY . '/')) {
            return false;
        }

        if (pathinfo($path, PATHINFO_EXTENSION) !== 'csv') {
            return false;
        }

        return collect(explode('/', $path))->doesntContain(
            fn (string $segment): bool => in_array($segment, ['', '.', '..'], true) || str_contains($segment, '\\'),
        );
    }
}
