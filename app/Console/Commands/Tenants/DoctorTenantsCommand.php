<?php

namespace App\Console\Commands\Tenants;

use App\Models\Platform\Tenant;
use App\Services\Tenants\TenantSQLiteDatabaseManager;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\note;
use function Laravel\Prompts\warning;

#[Signature('tenants:doctor
    {--fix : Interactively repair issues}
    {--force : Skip confirmation prompts (requires --fix)}
    {--seed : Also re-seed recreated tenant DBs (slow; default off)}')]
#[Description('Diagnose tenant rows vs SQLite files and optionally repair drift')]
class DoctorTenantsCommand extends Command
{
    public function handle(TenantSQLiteDatabaseManager $manager): int
    {
        $report = $this->scan();

        $this->info('Tenant diagnostic:');
        $this->line("  ✓ Healthy:        {$report['healthy']}");
        $this->line('  ✗ Orphan rows:    ' . count($report['orphanRows']) . '   (central row exists, SQLite file missing)');
        $this->line('  ✗ Orphan files:   ' . count($report['orphanFiles']) . ' (SQLite file exists, no central row)');

        if ($report['orphanRows'] === [] && $report['orphanFiles'] === []) {
            note('No drift detected.');

            return self::SUCCESS;
        }

        if (! $this->option('fix')) {
            $this->newLine();
            note('Re-run with --fix to repair (use --seed to also seed recreated DBs).');

            return self::SUCCESS;
        }

        $this->newLine();
        $this->repairOrphanRows($report['orphanRows'], $manager);
        $this->repairOrphanFiles($report['orphanFiles']);

        $this->newLine();
        $this->info('✅ Done.');

        return self::SUCCESS;
    }

    /**
     * @return array{healthy: int, orphanRows: list<Tenant>, orphanFiles: list<string>}
     */
    private function scan(): array
    {
        $orphanRows = [];
        $orphanFiles = [];
        $healthy = 0;

        $expectedFiles = [];
        foreach (Tenant::all() as $tenant) {
            /** @var Tenant $tenant */
            $name = (string) $tenant->database()->getName();
            $path = (string) database_path($name);
            $expectedFiles[$name] = true;

            if (file_exists($path)) {
                $healthy++;
            } else {
                $orphanRows[] = $tenant;
            }
        }

        foreach (glob((string) database_path('tenant*')) ?: [] as $file) {
            $name = basename($file);
            if (! isset($expectedFiles[$name])) {
                $orphanFiles[] = $file;
            }
        }

        return ['healthy' => $healthy, 'orphanRows' => $orphanRows, 'orphanFiles' => $orphanFiles];
    }

    /** @param  list<Tenant>  $orphans */
    private function repairOrphanRows(array $orphans, TenantSQLiteDatabaseManager $manager): void
    {
        if ($orphans === []) {
            return;
        }

        $this->info('Recreating SQLite databases for orphan rows…');

        foreach ($orphans as $tenant) {
            $this->line("  • {$tenant->id}");
            $manager->createDatabase($tenant);

            Artisan::call('tenants:migrate', [
                '--tenants' => [$tenant->id],
                '--force' => true,
            ]);

            if ($this->option('seed')) {
                $tenant->run(fn () => Artisan::call('db:seed', ['--force' => true]));
            }
        }

        if (! $this->option('seed')) {
            note('Recreated DBs are migrated but empty. Pass --seed next time to also seed them.');
        }
    }

    /** @param  list<string>  $files */
    private function repairOrphanFiles(array $files): void
    {
        if ($files === []) {
            return;
        }

        warning('Found ' . count($files) . ' SQLite file(s) with no matching tenant row.');

        if (! $this->option('force')) {
            if (! confirm(label: 'Delete these orphan SQLite files?', default: false)) {
                note('Skipped.');

                return;
            }
        }

        foreach ($files as $file) {
            $this->line('  • ' . basename($file));
            unlink($file);
        }
    }
}
