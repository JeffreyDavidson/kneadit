<?php

namespace App\Console\Commands\Operations;

use App\Actions\Content\CopyPrivateImagesToPublicDisk;
use App\Models\Platform\Tenant;
use App\Services\Settings\TenantSettings;
use App\Services\Tenants\TenancyManager;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('images:copy-private-to-public {--apply : Copy the files. Without this flag the command only reports what it would copy}')]
#[Description('Copy product and social post images uploaded to the private local disk onto the public disk (dry run by default, never deletes the source)')]
class CopyPrivateImagesToPublicDiskCommand extends Command
{
    public function handle(TenancyManager $tenancyManager, CopyPrivateImagesToPublicDisk $copy): int
    {
        $apply = (bool) $this->option('apply');
        $total = 0;

        $failures = $tenancyManager->forEachTenant(
            function (Tenant $tenant, TenantSettings $settings) use ($copy, $apply, &$total): void {
                $result = $copy($apply);

                foreach ($result['copied'] as $path) {
                    $this->line("{$tenant->id}: {$path}");
                }

                foreach ($result['missing'] as $path) {
                    $this->warn("{$tenant->id}: {$path} is not on the local disk or the public disk");
                }

                $total += count($result['copied']);
            },
        );

        $this->info(
            $apply
                ? "Copied {$total} files to the public disk."
                : "Dry run: {$total} files would be copied. Re-run with --apply to copy them.",
        );

        return $failures > 0 ? self::FAILURE : self::SUCCESS;
    }
}
