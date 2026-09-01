<?php

namespace App\Services\Tenants;

use App\Contracts\Tenants\LegacyCatalogImporter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use UnexpectedValueException;

final class DatabaseLegacyCatalogImporter implements LegacyCatalogImporter
{
    /**
     * @param array<int, array<string, mixed>> $categories
     * @param array<int, array<string, mixed>> $products
     * @return array{category_ids: array<int, int>, product_ids: array<int, int>}
     */
    public function import(array $categories, array $products): array
    {
        $categoryIds = $this->importCategories($categories);

        return [
            'category_ids' => $categoryIds,
            'product_ids' => $this->importProducts($products, $categoryIds),
        ];
    }

    /**
     * @param array<int, array<string, mixed>> $categories
     * @return array<int, int>
     */
    private function importCategories(array $categories): array
    {
        $ids = [];

        foreach ($categories as $category) {
            $slug = Str::slug($this->stringValue($category['name']));
            DB::table('categories')->updateOrInsert(
                ['slug' => $slug],
                [
                    'name' => $category['name'],
                    'description' => $category['description'] ?? null,
                    'is_active' => $category['is_active'] ?? true,
                    'sort_order' => $category['sort_order'] ?? 0,
                    'created_at' => $category['created_at'] ?? now(),
                    'updated_at' => $category['updated_at'] ?? now(),
                ],
            );
            $ids[$this->parseLegacyInteger($category['id'])] = $this->parseLegacyInteger(DB::table('categories')->where('slug', $slug)->value('id'));
        }

        return $ids;
    }

    /**
     * @param array<int, array<string, mixed>> $products
     * @param array<int, int> $categoryIds
     * @return array<int, int>
     */
    private function importProducts(array $products, array $categoryIds): array
    {
        $ids = [];

        foreach ($products as $product) {
            $slug = Str::slug($this->stringValue($product['name']));
            DB::table('products')->updateOrInsert(
                ['slug' => $slug],
                [
                    'name' => $product['name'],
                    'description' => $product['description'] ?? null,
                    'price' => $this->cents($product['price'] ?? 0),
                    'cost' => isset($product['cost']) ? $this->cents($product['cost']) : null,
                    'category_id' => $categoryIds[$this->parseLegacyInteger($product['category_id'])],
                    'is_active' => $product['is_available'] ?? $product['is_active'] ?? true,
                    'is_featured' => $product['is_featured'] ?? false,
                    'image' => $product['image'] ?? null,
                    'created_at' => $product['created_at'] ?? now(),
                    'updated_at' => $product['updated_at'] ?? now(),
                ],
            );
            $ids[$this->parseLegacyInteger($product['id'])] = $this->parseLegacyInteger(DB::table('products')->where('slug', $slug)->value('id'));
        }

        return $ids;
    }

    private function cents(mixed $dollars): int
    {
        return (int) round($this->floatValue($dollars) * 100);
    }

    private function stringValue(mixed $value): string
    {
        if (! is_string($value) && ! is_int($value) && ! is_float($value)) {
            throw new UnexpectedValueException('Expected a string-compatible legacy value.');
        }

        return (string) $value;
    }

    private function parseLegacyInteger(mixed $value): int
    {
        if (is_int($value)) {
            return $value;
        }

        if (! is_string($value) || filter_var($value, FILTER_VALIDATE_INT) === false) {
            throw new UnexpectedValueException('Expected an integer-compatible legacy value.');
        }

        return (int) $value;
    }

    private function floatValue(mixed $value): float
    {
        if (is_float($value) || is_int($value)) {
            return $value;
        }

        if (! is_string($value) || ! is_numeric($value)) {
            throw new UnexpectedValueException('Expected a numeric legacy value.');
        }

        return (float) $value;
    }
}
