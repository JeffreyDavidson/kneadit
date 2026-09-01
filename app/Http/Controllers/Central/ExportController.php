<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Platform\Tenant;
use App\Services\Export\CsvExportService;
use App\Services\Tenants\TenancyManager;
use Illuminate\Routing\Attributes\Controllers\Authorize;
use Illuminate\Support\Facades\File;
use RuntimeException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;
use ZipArchive;

class ExportController extends Controller
{
    #[Authorize('platform-admin')]
    public function __invoke(
        string $tenantId,
        string $type,
        CsvExportService $csvExport,
        TenancyManager $tenancyManager,
    ): StreamedResponse|BinaryFileResponse {
        /** @var array<int, string> $validTypes */
        $validTypes = [...$csvExport->validTypes(), 'all'];
        abort_unless(in_array($type, $validTypes, true), 404, 'Invalid export type.');

        $tenant = Tenant::query()->findOrFail($tenantId);

        if ($type === 'all') {
            return $this->exportAll($tenant, $csvExport, $tenancyManager);
        }

        return $this->streamCsv($tenant, $type, $csvExport, $tenancyManager);
    }

    private function streamCsv(Tenant $tenant, string $type, CsvExportService $csvExport, TenancyManager $tenancyManager): StreamedResponse
    {
        $filename = "{$tenant->id}_{$type}_" . now()->format('Y-m-d_His') . '.csv';

        return response()->streamDownload(function () use ($tenant, $type, $csvExport, $tenancyManager) {
            $tenancyManager->withinTenant($tenant, function () use ($type, $csvExport) {
                $handle = fopen('php://output', 'w');
                throw_if($handle === false, RuntimeException::class, 'Failed to open file handle');

                $csvExport->writeTo($handle, $type);
                fclose($handle);
            });
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    private function exportAll(Tenant $tenant, CsvExportService $csvExport, TenancyManager $tenancyManager): StreamedResponse
    {
        $filename = "{$tenant->id}_all_data_" . now()->format('Y-m-d_His') . '.zip';

        return response()->streamDownload(function () use ($tenant, $csvExport, $tenancyManager) {
            $tmpFile = tempnam(sys_get_temp_dir(), 'export_');
            throw_if($tmpFile === false, RuntimeException::class, 'Failed to create temporary export file.');

            $zip = new ZipArchive;
            $openResult = $zip->open($tmpFile, ZipArchive::CREATE | ZipArchive::OVERWRITE);

            if ($openResult !== true) {
                File::delete($tmpFile);

                throw new RuntimeException("Failed to open temporary export archive (code {$openResult}).");
            }

            try {
                $tenancyManager->withinTenant($tenant, function () use ($csvExport, $zip): void {
                    foreach ($csvExport->validTypes() as $type) {
                        throw_unless(
                            $zip->addFromString("{$type}.csv", $csvExport->toString($type)),
                            RuntimeException::class,
                            "Failed to add {$type} export to archive.",
                        );
                    }
                });

                throw_unless(
                    $zip->close(),
                    RuntimeException::class,
                    'Failed to finalize temporary export archive.',
                );

                $handle = fopen($tmpFile, 'rb');
                throw_if($handle === false, RuntimeException::class, 'Failed to open temporary export archive.');

                try {
                    $bytes = fpassthru($handle);
                    throw_if($bytes < 1, RuntimeException::class, 'Failed to stream export archive.');
                } finally {
                    fclose($handle);
                }
            } catch (Throwable $e) {
                $zip->close();

                throw $e;
            } finally {
                File::delete($tmpFile);
            }
        }, $filename, [
            'Content-Type' => 'application/zip',
        ]);
    }
}
