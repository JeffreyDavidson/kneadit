<?php

namespace App\Actions\Inventory;

use App\Models\Category;
use App\Models\Product;
use App\Services\Export\ProductCsvExporter;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class ImportProducts
{
    public function __construct(
        protected ProductCsvExporter $exporter,
    ) {}

    /** @return array{created: int, updated: int, errors: array<int, string>} */
    public function __invoke(UploadedFile $file): array
    {
        $parsed = $this->exporter->parseForPreview($file);

        if (! empty($parsed['errors'])) {
            return ['created' => 0, 'updated' => 0, 'errors' => $parsed['errors']];
        }

        $created = 0;
        $updated = 0;
        $errors = [];
        $categoryCache = [];

        foreach ($parsed['rows'] as $row) {
            /** @var array<string, mixed> $row */
            if (! empty($row['_errors'])) {
                $errors[] = 'Row '.(int) $row['_line'].': '.implode(', ', (array) $row['_errors']);

                continue;
            }

            try {
                $categoryId = null;
                $categoryName = trim((string) ($row['category'] ?? ''));
                if ($categoryName !== '') {
                    if (! isset($categoryCache[$categoryName])) {
                        $categoryCache[$categoryName] = Category::query()->firstOrCreate(
                            ['name' => $categoryName],
                            ['slug' => Str::slug($categoryName)]
                        )->id;
                    }
                    $categoryId = $categoryCache[$categoryName];
                }

                $existing = Product::query()->where('name', trim((string) ($row['name'] ?? '')))->first();

                $data = [
                    'name' => trim((string) ($row['name'] ?? '')),
                    'slug' => Str::slug(trim((string) ($row['name'] ?? ''))),
                    'description' => trim((string) ($row['description'] ?? '')),
                    'price' => (float) ($row['price'] ?? 0),
                    'category_id' => $categoryId,
                    'is_active' => (bool) ($row['is_active'] ?? true),
                    'is_featured' => (bool) ($row['is_featured'] ?? false),
                ];

                if (isset($row['cost']) && $row['cost'] !== '') {
                    $data['cost'] = (float) ($row['cost'] ?? 0);
                }

                if ($existing) {
                    $existing->update($data);
                    $updated++;
                } else {
                    Product::query()->create($data);
                    $created++;
                }
            } catch (\Throwable $e) {
                $errors[] = 'Row '.(int) $row['_line'].': '.$e->getMessage();
            }
        }

        return compact('created', 'updated', 'errors');
    }
}
