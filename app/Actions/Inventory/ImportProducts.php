<?php

namespace App\Actions\Inventory;

use App\Models\Inventory\Category;
use App\Models\Inventory\Product;
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
            $line = $this->integerValue($row['_line'] ?? null);
            $rowErrors = $this->stringList($row['_errors'] ?? []);

            if ($rowErrors !== []) {
                $errors[] = "Row {$line}: " . implode(', ', $rowErrors);

                continue;
            }

            try {
                $categoryId = null;
                $categoryName = trim($this->stringValue($row['category'] ?? ''));
                if ($categoryName !== '') {
                    if (! isset($categoryCache[$categoryName])) {
                        $categoryCache[$categoryName] = Category::query()->firstOrCreate(
                            ['name' => $categoryName],
                            ['slug' => Str::slug($categoryName)],
                        )->id;
                    }
                    $categoryId = $categoryCache[$categoryName];
                }

                $name = trim($this->stringValue($row['name'] ?? null));
                $existing = Product::query()->where('name', $name)->first();

                $data = [
                    'name' => $name,
                    'slug' => Str::slug($name),
                    'description' => trim($this->stringValue($row['description'] ?? '')),
                    'price' => $this->floatValue($row['price'] ?? null),
                    'category_id' => $categoryId,
                    'is_active' => (bool) ($row['is_active'] ?? true),
                    'is_featured' => (bool) ($row['is_featured'] ?? false),
                ];

                if (isset($row['cost']) && $row['cost'] !== '') {
                    $data['cost'] = $this->floatValue($row['cost']);
                }

                if ($existing) {
                    $existing->update($data);
                    $updated++;
                } else {
                    Product::query()->create($data);
                    $created++;
                }
            } catch (\Throwable $e) {
                $errors[] = "Row {$line}: {$e->getMessage()}";
            }
        }

        return ['created' => $created, 'updated' => $updated, 'errors' => $errors];
    }

    private function stringValue(mixed $value): string
    {
        if (! is_string($value)) {
            throw new \UnexpectedValueException('Expected a CSV string value.');
        }

        return $value;
    }

    private function integerValue(mixed $value): int
    {
        if (! is_int($value)) {
            throw new \UnexpectedValueException('Expected a CSV row number.');
        }

        return $value;
    }

    private function floatValue(mixed $value): float
    {
        if (! is_string($value) || ! is_numeric($value)) {
            throw new \UnexpectedValueException('Expected a numeric CSV value.');
        }

        return (float) $value;
    }

    /** @return array<int, string> */
    private function stringList(mixed $value): array
    {
        if (! is_array($value)) {
            throw new \UnexpectedValueException('Expected a list of CSV validation errors.');
        }

        $errors = [];

        foreach ($value as $error) {
            $errors[] = $this->stringValue($error);
        }

        return $errors;
    }
}
