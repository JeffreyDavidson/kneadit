<?php

namespace App\Services\Export;

use App\Models\Platform\Tenant;
use App\Services\Tenants\TenancyManager;
use Illuminate\Support\Facades\File;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;

final readonly class TenantExportResponseFactory
{
    public function __construct(
        private CsvExportService $csvExport,
        private TenantArchiveExporter $archiveExporter,
        private TenancyManager $tenancyManager,
    ) {}

    public function csv(Tenant $tenant, string $type): StreamedResponse
    {
        $filename = "{$tenant->id}_{$type}_".now()->format('Y-m-d_His').'.csv';

        return response()->streamDownload(function () use ($tenant, $type): void {
            $this->tenancyManager->withinTenant($tenant, function () use ($type): void {
                $handle = fopen('php://output', 'w');
                throw_if($handle === false, RuntimeException::class, 'Failed to open file handle');

                $this->csvExport->writeTo($handle, $type);
                fclose($handle);
            });
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function archive(Tenant $tenant): StreamedResponse
    {
        $filename = "{$tenant->id}_all_data_".now()->format('Y-m-d_His').'.zip';

        return response()->streamDownload(function () use ($tenant): void {
            $tmpFile = $this->archiveExporter->create($tenant);
            try {
                $handle = fopen($tmpFile, 'rb');
                throw_if($handle === false, RuntimeException::class, 'Failed to open temporary export archive.');

                try {
                    $bytes = fpassthru($handle);
                    throw_if($bytes < 1, RuntimeException::class, 'Failed to stream export archive.');
                } finally {
                    fclose($handle);
                }
            } finally {
                File::delete($tmpFile);
            }
        }, $filename, [
            'Content-Type' => 'application/zip',
        ]);
    }
}
