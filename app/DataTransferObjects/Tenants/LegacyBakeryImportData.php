<?php

namespace App\DataTransferObjects\Tenants;

use InvalidArgumentException;

/**
 * @phpstan-type DatasetRecords list<array<string, mixed>>
 */
final readonly class LegacyBakeryImportData
{
    /**
     * @param array<string, DatasetRecords> $datasets
     */
    private function __construct(
        private array $datasets,
    ) {}

    public static function from(mixed $data): self
    {
        if (! is_array($data)) {
            throw new InvalidArgumentException('The import file must contain an object of dataset arrays.');
        }

        /** @var array<string, DatasetRecords> $datasets */
        $datasets = [];

        foreach ($data as $dataset => $records) {
            if (! is_string($dataset) || ! is_array($records) || ! array_is_list($records)) {
                throw new InvalidArgumentException('The import file must contain an object of dataset arrays.');
            }

            foreach ($records as $record) {
                if (! is_array($record)) {
                    throw new InvalidArgumentException('The import file must contain an object of dataset arrays.');
                }
            }

            /** @var DatasetRecords $records */
            $datasets[$dataset] = $records;
        }

        return new self($datasets);
    }

    /**
     * @return array<string, DatasetRecords>
     */
    public function toArray(): array
    {
        return $this->datasets;
    }

    /**
     * @return array<string, int>
     */
    public function counts(): array
    {
        $counts = [];

        foreach ($this->datasets as $dataset => $records) {
            $counts[$dataset] = count($records);
        }

        return $counts;
    }
}
