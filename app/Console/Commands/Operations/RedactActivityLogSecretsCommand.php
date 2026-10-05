<?php

namespace App\Console\Commands\Operations;

use App\Models\Operations\ActivityLog;
use App\Models\Platform\Tenant;
use App\Services\Audit\ActivityLogRedactor;
use App\Services\Tenants\TenancyManager;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;

/**
 * One-off clean-up for rows written before the activity log redacted secrets.
 * Safe to run again: a row with nothing left to redact is not touched.
 */
#[Signature('activity-log:redact-secrets')]
#[Description('Redact password hashes, remember tokens and other secrets already stored in activity log changes, across all tenants')]
class RedactActivityLogSecretsCommand extends Command
{
    /** @var array<string, list<string>> */
    private array $hiddenByModel = [];

    public function handle(TenancyManager $tenancy, ActivityLogRedactor $redactor): int
    {
        $failures = $tenancy->forEachTenant(function (Tenant $tenant) use ($redactor): void {
            $redacted = 0;

            ActivityLog::query()
                ->whereNotNull('properties')
                ->chunkById(500, function ($logs) use ($redactor, &$redacted): void {
                    foreach ($logs as $log) {
                        $properties = $log->properties;

                        if ($properties === null) {
                            continue;
                        }

                        $clean = $redactor->redactProperties($properties, $this->hiddenFor($log->model_type));

                        if ($clean === $properties) {
                            continue;
                        }

                        $log->update(['properties' => $clean]);
                        $redacted++;
                    }
                });

            $this->info("{$tenant->id}: Redacted {$redacted} row(s)");
        });

        return $failures > 0 ? self::FAILURE : self::SUCCESS;
    }

    /** @return list<string> */
    private function hiddenFor(string $modelType): array
    {
        return $this->hiddenByModel[$modelType] ??= is_subclass_of($modelType, Model::class)
            ? array_values(new $modelType()->getHidden())
            : [];
    }
}
