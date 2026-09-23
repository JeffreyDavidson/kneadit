<?php

declare(strict_types=1);

namespace App\Console\Commands\Tenants;

use Illuminate\Console\Attributes\Signature;
use Stancl\Tenancy\Commands\Seed;

#[Signature('tenants:seed
    {class? : The class name of the root seeder}
    {--class=Database\\Seeders\\DatabaseSeeder : The class name of the root seeder}
    {--database= : The database connection to seed}
    {--force : Force the operation to run when in production}
    {--tenants=* : The tenant(s) to run the command for. Default: all}')]
class SeedTenantDatabasesCommand extends Seed {}
