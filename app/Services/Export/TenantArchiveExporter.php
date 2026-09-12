<?php

namespace App\Services\Export;

use App\Models\Platform\Tenant;
use App\Services\Tenants\TenancyManager;
use Illuminate\Support\Facades\File;
use RuntimeException;
use Throwable;
use ZipArchive;

class TenantArchiveExporter
{
    public function __construct(
        private readonly CsvExportService $csvExport,
        private readonly TenancyManager $tenancyManager,
    ) {}

    /** Create a temporary ZIP archive containing every export for a tenant. */
    public function create(Tenant $tenant): string
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'export_');
        throw_if($tmpFile === false, RuntimeException::class, 'Failed to create temporary export file.');

        $zip = new ZipArchive;
        $openResult = $zip->open($tmpFile, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        if ($openResult !== true) {
            File::delete($tmpFile);

            throw new RuntimeException("Failed to open temporary export archive (code {$openResult}).");
        }

        try {
            $this->tenancyManager->withinTenant($tenant, function () use ($zip): void {
                foreach ($this->csvExport->validTypes() as $type) {
                    throw_unless(
                        $zip->addFromString("{$type}.csv", $this->csvExport->toString($type)),
                        RuntimeException::class,
                        "Failed to add {$type} export to archive.",
                    );
                }
            });

            throw_unless($zip->close(), RuntimeException::class, 'Failed to finalize temporary export archive.');

            return $tmpFile;
        } catch (Throwable $e) {
            $zip->close();
            File::delete($tmpFile);

            throw $e;
        }
    }
}
