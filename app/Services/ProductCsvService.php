<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class ProductCsvService
{
    protected array $headers = [
        'name',
        'category',
        'description',
        'price',
        'cost',
        'is_active',
        'is_featured',
    ];

    public function getTemplateContent(): string
    {
        return implode(',', $this->headers)."\n";
    }

    public function export(): string
    {
        $output = fopen('php://temp', 'r+');
        fputcsv($output, $this->headers);

        Product::with('category')->orderBy('name')->each(function (Product $product) use ($output) {
            fputcsv($output, [
                $product->name,
                $product->category->name ?? '',
                $product->description ?? '',
                $product->price,
                $product->cost ?? '',
                $product->is_active ? '1' : '0',
                $product->is_featured ? '1' : '0',
            ]);
        });

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv;
    }

    public function parseForPreview(UploadedFile $file): array
    {
        $rows = [];
        $errors = [];

        $handle = fopen($file->getRealPath(), 'r');
        $header = fgetcsv($handle);

        if (! $header) {
            return ['rows' => [], 'errors' => ['CSV file is empty.']];
        }

        $header = array_map(fn (string $h) => strtolower(trim($h)), $header);
        $missing = array_diff(['name', 'price'], $header);

        if (! empty($missing)) {
            fclose($handle);

            return ['rows' => [], 'errors' => ['Missing required columns: '.implode(', ', $missing)]];
        }

        $line = 1;
        while (($row = fgetcsv($handle)) !== false) {
            $line++;
            if (count($row) !== count($header)) {
                $errors[] = "Row {$line}: column count mismatch.";

                continue;
            }
            $mapped = array_combine($header, $row);
            $rowErrors = [];

            if (empty(trim($mapped['name'] ?? ''))) {
                $rowErrors[] = 'Name is required';
            }
            $price = $mapped['price'] ?? '';
            if ($price === '' || ! is_numeric($price) || (float) $price < 0) {
                $rowErrors[] = 'Invalid price';
            }
            if (isset($mapped['cost']) && $mapped['cost'] !== '' && ! is_numeric($mapped['cost'])) {
                $rowErrors[] = 'Invalid cost';
            }

            $mapped['_line'] = $line;
            $mapped['_errors'] = $rowErrors;
            $rows[] = $mapped;

            if (! empty($rowErrors)) {
                $errors[] = "Row {$line}: ".implode(', ', $rowErrors);
            }
        }

        fclose($handle);

        return ['rows' => $rows, 'errors' => $errors];
    }

    public function import(UploadedFile $file): array
    {
        $parsed = $this->parseForPreview($file);

        if (! empty($parsed['errors'])) {
            return ['created' => 0, 'updated' => 0, 'errors' => $parsed['errors']];
        }

        $created = 0;
        $updated = 0;
        $errors = [];
        $categoryCache = [];

        foreach ($parsed['rows'] as $row) {
            if (! empty($row['_errors'])) {
                $errors[] = "Row {$row['_line']}: ".implode(', ', $row['_errors']);

                continue;
            }

            try {
                $categoryId = null;
                $categoryName = trim($row['category'] ?? '');
                if ($categoryName !== '') {
                    if (! isset($categoryCache[$categoryName])) {
                        $categoryCache[$categoryName] = Category::query()->firstOrCreate(['name' => $categoryName], ['slug' => Str::slug($categoryName)])->id;
                    }
                    $categoryId = $categoryCache[$categoryName];
                }

                $existing = Product::query()->where('name', trim($row['name']))->first();

                $data = [
                    'name' => trim($row['name']),
                    'slug' => Str::slug(trim($row['name'])),
                    'description' => trim($row['description'] ?? ''),
                    'price' => (float) $row['price'],
                    'category_id' => $categoryId,
                    'is_active' => (bool) ($row['is_active'] ?? true),
                    'is_featured' => (bool) ($row['is_featured'] ?? false),
                ];

                if (isset($row['cost']) && $row['cost'] !== '') {
                    $data['cost'] = (float) $row['cost'];
                }

                if ($existing) {
                    $existing->update($data);
                    $updated++;
                } else {
                    Product::query()->create($data);
                    $created++;
                }
            } catch (\Throwable $e) {
                $errors[] = "Row {$row['_line']}: {$e->getMessage()}";
            }
        }

        return compact('created', 'updated', 'errors');
    }
}
