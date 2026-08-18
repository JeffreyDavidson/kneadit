<?php

namespace App\Services\Inventory;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductCsvUploadResolver
{
    public function resolve(string $path): ?UploadedFile
    {
        if (! $this->isExpectedPath($path)) {
            return null;
        }

        $disk = Storage::disk('imports');
        $importRoot = realpath($disk->path(''));
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
        if ($path !== basename($path)) {
            return false;
        }

        if (pathinfo($path, PATHINFO_EXTENSION) !== 'csv') {
            return false;
        }

        return $path !== '' && ! str_contains($path, '\\');
    }
}
