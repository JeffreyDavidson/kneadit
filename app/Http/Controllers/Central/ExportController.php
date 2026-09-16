<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Platform\Tenant;
use App\Services\Export\CsvExportService;
use App\Services\Export\TenantExportResponseFactory;
use Illuminate\Routing\Attributes\Controllers\Authorize;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    #[Authorize('platform-admin')]
    public function __invoke(
        string $tenantId,
        string $type,
        CsvExportService $csvExport,
        TenantExportResponseFactory $responseFactory,
    ): StreamedResponse|BinaryFileResponse {
        /** @var array<int, string> $validTypes */
        $validTypes = [...$csvExport->validTypes(), 'all'];
        abort_unless(in_array($type, $validTypes, true), 404, 'Invalid export type.');

        $tenant = Tenant::query()->findOrFail($tenantId);

        if ($type === 'all') {
            return $responseFactory->archive($tenant);
        }

        return $responseFactory->csv($tenant, $type);
    }
}
