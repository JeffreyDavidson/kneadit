<?php

namespace App\Console\Commands\Tenants;

use App\Actions\Tenants\ImportLegacyBakeryAssets;
use App\Actions\Tenants\ImportLegacyBakeryData;
use App\Models\Platform\Tenant;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('tenant:import-legacy-bakery {tenant : Existing KneadIt tenant ID} {file : Path to the legacy JSON export} {--assets= : Path to the legacy public asset directory} {--dry-run : Validate and summarize without writing data}')]
#[Description('Import a legacy Bakery on Biscotto dataset into an existing KneadIt tenant')]
class ImportLegacyBakeryCommand extends Command
{
    public function handle(ImportLegacyBakeryData $import, ImportLegacyBakeryAssets $importAssets): int
    {
        $path = realpath($this->argument('file'));

        if ($path === false || ! is_file($path)) {
            $this->error('The import file does not exist.');

            return self::FAILURE;
        }

        try {
            /** @var array<string, array<int, array<string, mixed>>> $data */
            $data = json_decode((string) file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
        } catch (\JsonException $exception) {
            $this->error("The import file is not valid JSON: {$exception->getMessage()}");

            return self::FAILURE;
        }

        $counts = [];
        foreach ($data as $name => $records) {
            $counts[$name] = count($records);
        }

        if ($this->option('dry-run')) {
            $this->table(['Dataset', 'Records'], $this->tableRows($counts));

            return self::SUCCESS;
        }

        $tenant = Tenant::query()->find($this->argument('tenant'));

        if (! $tenant) {
            $this->error('The target tenant does not exist.');

            return self::FAILURE;
        }

        $assetDirectory = $this->option('assets');

        if (! is_string($assetDirectory) || $assetDirectory === '') {
            $this->error('The --assets option is required for a complete Bakery on Biscotto import.');

            return self::FAILURE;
        }

        try {
            $assetImport = $importAssets($data, $assetDirectory, $tenant->id);
            $data = $assetImport['data'];
        } catch (\InvalidArgumentException $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $tenant->update([
            'store_name' => 'Bakery on Biscotto',
            'brand_color_primary' => '#8b5e3c',
            'brand_color_secondary' => '#d4a574',
            'store_logo' => $assetImport['store_logo'],
            'storefront_enabled' => true,
            'is_active' => true,
        ]);

        /** @var array<string, int> $result */
        $result = $tenant->run(fn (): array => $import($data));
        $this->table(['Imported dataset', 'Records'], $this->tableRows($result));

        return self::SUCCESS;
    }

    /**
     * @param array<string, int> $counts
     * @return array<int, array{string, int}>
     */
    private function tableRows(array $counts): array
    {
        $rows = [];

        foreach ($counts as $name => $count) {
            $rows[] = [$name, $count];
        }

        return $rows;
    }
}
