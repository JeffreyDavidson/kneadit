<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    private const VALID_TYPES = ['products', 'categories', 'orders', 'customers', 'reviews', 'all'];

    public function __invoke(Request $request, string $tenantId, string $type): StreamedResponse|BinaryFileResponse
    {
        abort_if(! auth()->check() || auth()->user()->role !== 'platform_admin', 403, 'Unauthorized.');

        abort_unless(in_array($type, self::VALID_TYPES), 404, 'Invalid export type.');

        $tenant = Tenant::findOrFail($tenantId);

        if ($type === 'all') {
            return $this->exportAll($tenant);
        }

        return $this->streamCsv($tenant, $type);
    }

    private function streamCsv(Tenant $tenant, string $type): StreamedResponse
    {
        $filename = "{$tenant->id}_{$type}_".now()->format('Y-m-d_His').'.csv';

        return response()->streamDownload(function () use ($tenant, $type) {
            try {
                tenancy()->initialize($tenant);
                $handle = fopen('php://output', 'w');

                match ($type) {
                    'products' => $this->writeProducts($handle),
                    'categories' => $this->writeCategories($handle),
                    'orders' => $this->writeOrders($handle),
                    'customers' => $this->writeCustomers($handle),
                    'reviews' => $this->writeReviews($handle),
                };

                fclose($handle);
                tenancy()->end();
            } catch (\Throwable $e) {
                tenancy()->end();
                throw $e;
            }
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    private function exportAll(Tenant $tenant): StreamedResponse
    {
        $filename = "{$tenant->id}_all_data_".now()->format('Y-m-d_His').'.zip';

        return response()->streamDownload(function () use ($tenant) {
            $tmpFile = tempnam(sys_get_temp_dir(), 'export_');
            $zip = new \ZipArchive;
            $zip->open($tmpFile, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

            try {
                tenancy()->initialize($tenant);

                foreach (['products', 'categories', 'orders', 'customers', 'reviews'] as $type) {
                    $csv = $this->generateCsvString($type);
                    $zip->addFromString("{$type}.csv", $csv);
                }

                tenancy()->end();
            } catch (\Throwable $e) {
                tenancy()->end();
                $zip->close();
                @unlink($tmpFile);
                throw $e;
            }

            $zip->close();
            readfile($tmpFile);
            @unlink($tmpFile);
        }, $filename, [
            'Content-Type' => 'application/zip',
        ]);
    }

    private function generateCsvString(string $type): string
    {
        $handle = fopen('php://temp', 'r+');

        match ($type) {
            'products' => $this->writeProducts($handle),
            'categories' => $this->writeCategories($handle),
            'orders' => $this->writeOrders($handle),
            'customers' => $this->writeCustomers($handle),
            'reviews' => $this->writeReviews($handle),
        };

        rewind($handle);
        $content = stream_get_contents($handle);
        fclose($handle);

        return $content;
    }

    /**
     * @param  resource  $handle
     */
    private function writeProducts($handle): void
    {
        fputcsv($handle, ['ID', 'Name', 'Slug', 'Description', 'Price', 'Status', 'Created At', 'Updated At']);
        DB::table('products')->orderBy('id')->chunk(500, function (Collection $rows) use ($handle) {
            foreach ($rows as $row) {
                fputcsv($handle, [
                    $row->id, $row->name, $row->slug ?? '', $row->description ?? '',
                    $row->price ?? '', $row->status ?? '', $row->created_at, $row->updated_at,
                ]);
            }
        });
    }

    /**
     * @param  resource  $handle
     */
    private function writeCategories($handle): void
    {
        fputcsv($handle, ['ID', 'Name', 'Slug', 'Description', 'Created At', 'Updated At']);
        DB::table('categories')->orderBy('id')->chunk(500, function (Collection $rows) use ($handle) {
            foreach ($rows as $row) {
                fputcsv($handle, [
                    $row->id, $row->name, $row->slug ?? '', $row->description ?? '',
                    $row->created_at, $row->updated_at,
                ]);
            }
        });
    }

    /**
     * @param  resource  $handle
     */
    private function writeOrders($handle): void
    {
        fputcsv($handle, ['Order ID', 'Customer ID', 'Status', 'Total', 'Item Product ID', 'Item Qty', 'Item Unit Price', 'Order Created At']);
        DB::table('orders')
            ->leftJoin('order_items', 'orders.id', '=', 'order_items.order_id')
            ->select('orders.*', 'order_items.product_id as item_product_id', 'order_items.quantity as item_qty', 'order_items.unit_price as item_price')
            ->orderBy('orders.id')
            ->chunk(500, function (Collection $rows) use ($handle) {
                foreach ($rows as $row) {
                    fputcsv($handle, [
                        $row->id, $row->user_id ?? '', $row->status ?? '', $row->total ?? '',
                        $row->item_product_id ?? '', $row->item_qty ?? '', $row->item_price ?? '',
                        $row->created_at,
                    ]);
                }
            });
    }

    /**
     * @param  resource  $handle
     */
    private function writeCustomers($handle): void
    {
        fputcsv($handle, ['ID', 'Name', 'Email', 'Created At', 'Updated At']);
        DB::table('users')->orderBy('id')->chunk(500, function (Collection $rows) use ($handle) {
            foreach ($rows as $row) {
                fputcsv($handle, [
                    $row->id, $row->name, $row->email, $row->created_at, $row->updated_at,
                ]);
            }
        });
    }

    /**
     * @param  resource  $handle
     */
    private function writeReviews($handle): void
    {
        fputcsv($handle, ['ID', 'Product ID', 'User ID', 'Rating', 'Comment', 'Created At', 'Updated At']);
        DB::table('reviews')->orderBy('id')->chunk(500, function (Collection $rows) use ($handle) {
            foreach ($rows as $row) {
                fputcsv($handle, [
                    $row->id, $row->product_id ?? '', $row->user_id ?? '', $row->rating ?? '',
                    $row->comment ?? $row->body ?? '', $row->created_at, $row->updated_at,
                ]);
            }
        });
    }
}
