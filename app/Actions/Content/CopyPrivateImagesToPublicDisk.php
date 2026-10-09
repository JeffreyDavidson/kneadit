<?php

namespace App\Actions\Content;

use App\Models\Content\SocialPost;
use App\Models\Inventory\Product;
use App\Models\Inventory\ProductImage;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;

/**
 * Copies product and social post images that were uploaded to the private local disk onto the
 * public disk. Sources are never deleted. Runs inside the current tenant context.
 */
class CopyPrivateImagesToPublicDisk
{
    /**
     * @return array{copied: list<string>, missing: list<string>}
     */
    public function __invoke(bool $apply): array
    {
        $result = ['copied' => [], 'missing' => []];

        foreach ($this->imagePaths() as $path) {
            if (Storage::disk('public')->exists($path)) {
                continue;
            }

            if (! Storage::disk('local')->exists($path)) {
                $result['missing'][] = $path;

                continue;
            }

            if ($apply) {
                Storage::disk('public')->putFileAs(
                    dirname($path) === '.' ? '' : dirname($path),
                    new File(Storage::disk('local')->path($path)),
                    basename($path),
                    'public',
                );
            }

            $result['copied'][] = $path;
        }

        return $result;
    }

    /**
     * @return array<int, string>
     */
    private function imagePaths(): array
    {
        return collect()
            ->merge(ProductImage::query()->pluck('path'))
            ->merge(Product::query()->whereNotNull('image')->pluck('image'))
            ->merge(SocialPost::query()->whereNotNull('image_path')->pluck('image_path'))
            ->filter(fn (mixed $path): bool => is_string($path) && $path !== '')
            ->unique()
            ->values()
            ->all();
    }
}
