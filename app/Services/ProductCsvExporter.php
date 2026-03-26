<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Http\UploadedFile;

class ProductCsvExporter
{
    /** @var array<int, string> */
    protected array $headers = ['name', 'category', 'description', 'price', 'cost', 'is_active', 'is_featured'];

    public function getTemplateContent(): string
    {
        return implode(',', $this->headers)."\n";
    }

    public function export(): string
    {
        $output = fopen('php://temp', 'r+');
        if ($output === false) {
            throw new \RuntimeException('Failed to open file');
        }
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

    /** @return array{rows: array<int, array<string, mixed>>, errors: array<int, string>} */
    public function parseForPreview(UploadedFile $file): array
    {
        $rows = [];
        $errors = [];

        $handle = fopen($file->getRealPath(), 'r');
        if ($handle === false) {
            return ['rows' => [], 'errors' => ['Failed to read CSV file.']];
        }
        $header = fgetcsv($handle);

        if (! $header) {
            return ['rows' => [], 'errors' => ['CSV file is empty.']];
        }

        $header = array_map(fn (?string $h) => strtolower(trim($h ?? '')), $header);
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
}
