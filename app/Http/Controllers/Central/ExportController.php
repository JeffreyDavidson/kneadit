<?php

namespace App\Http\Controllers\Central;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Services\Export\CsvExportService;
use App\Services\Tenant\TenancyManager;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    public function __invoke(
        Request $request,
        string $tenantId,
        string $type,
        CsvExportService $csvExport,
        TenancyManager $tenancyManager,
    ): StreamedResponse|BinaryFileResponse {
        abort_if(! auth()->check() || auth()->user()?->role !== UserRole::PlatformAdmin, 403, 'Unauthorized.');

        $validTypes = [...$csvExport->validTypes(), 'all'];
        if (! in_array($type, $validTypes, true)) {
            abort(404, 'Invalid export type.');
        }

        $tenant = Tenant::query()->findOrFail($tenantId);

        if ($type === 'all') {
            return $this->exportAll($tenant, $csvExport, $tenancyManager);
        }

        return $this->streamCsv($tenant, $type, $csvExport, $tenancyManager);
    }

    private function streamCsv(Tenant $tenant, string $type, CsvExportService $csvExport, TenancyManager $tenancyManager): StreamedResponse
    {
        $filename = "{$tenant->id}_{$type}_".now()->format('Y-m-d_His').'.csv';

        return response()->streamDownload(function () use ($tenant, $type, $csvExport, $tenancyManager) {
            $tenancyManager->withinTenant($tenant, function () use ($type, $csvExport) {
                $handle = fopen('php://output', 'w');
                if ($handle === false) {
                    throw new \RuntimeException('Failed to open file handle');
                }

                $csvExport->writeTo($handle, $type);
                fclose($handle);
            });
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    private function exportAll(Tenant $tenant, CsvExportService $csvExport, TenancyManager $tenancyManager): StreamedResponse
    {
        $filename = "{$tenant->id}_all_data_".now()->format('Y-m-d_His').'.zip';

        return response()->streamDownload(function () use ($tenant, $csvExport, $tenancyManager) {
            $tmpFile = tempnam(sys_get_temp_dir(), 'export_');
            $zip = new \ZipArchive;
            $zip->open($tmpFile, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

            try {
                $tenancyManager->withinTenant($tenant, function () use ($csvExport, $zip) {
                    foreach ($csvExport->validTypes() as $type) {
                        $zip->addFromString("{$type}.csv", $csvExport->toString($type));
                    }
                });
            } catch (\Throwable $e) {
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
}
