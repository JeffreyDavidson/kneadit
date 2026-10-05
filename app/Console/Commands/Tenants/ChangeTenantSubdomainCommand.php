<?php

namespace App\Console\Commands\Tenants;

use App\Actions\Tenants\ChangeTenantSubdomain;
use App\Models\Platform\Tenant;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

#[Signature('tenants:change-subdomain {tenant : The bakery id} {subdomain : The new subdomain label}')]
#[Description('Move a bakery to a new subdomain; the old one keeps working and redirects to it')]
class ChangeTenantSubdomainCommand extends Command
{
    public function handle(ChangeTenantSubdomain $changeSubdomain): int
    {
        $tenant = Tenant::query()->find($this->argument('tenant'));

        if (! $tenant instanceof Tenant) {
            $this->error("No bakery with id \"{$this->argument('tenant')}\".");

            return self::FAILURE;
        }

        $subdomain = Str::lower(trim((string) $this->argument('subdomain')));

        if ($tenant->subdomain === $subdomain) {
            $this->info("{$tenant->id} is already on the subdomain \"{$subdomain}\".");

            return self::SUCCESS;
        }

        try {
            $changeSubdomain($tenant, $subdomain);
        } catch (ValidationException $exception) {
            $this->error($exception->validator->errors()->first());

            return self::FAILURE;
        }

        $this->info("{$tenant->id} now uses the subdomain \"{$subdomain}\"; the old one redirects to it.");

        return self::SUCCESS;
    }
}
