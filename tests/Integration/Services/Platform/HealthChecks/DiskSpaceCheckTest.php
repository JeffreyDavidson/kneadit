<?php

use App\Services\Platform\HealthChecks\DiskSpaceCheck;

const DISK_TEST_GIGABYTE = 1073741824;

/** A check that sees a disk of the given size instead of the real volume. */
function diskSpaceCheckWith(int $freeBytes, int $totalBytes): DiskSpaceCheck
{
    return new class($freeBytes, $totalBytes) extends DiskSpaceCheck
    {
        public function __construct(private readonly int $free, private readonly int $total) {}

        protected function freeBytes(): int
        {
            return $this->free;
        }

        protected function totalBytes(): int
        {
            return $this->total;
        }
    };
}

test('it reports the free disk space of the application volume', function () {
    $freeGb = round((disk_free_space(base_path()) ?: 0) / DISK_TEST_GIGABYTE, 1);

    $result = (new DiskSpaceCheck)->run();

    expect($result->message)->toContain("{$freeGb} GB free");
});

test('it passes when at least 20% and 5 GB are free', function (int $freeGb, int $totalGb) {
    $result = diskSpaceCheckWith($freeGb * DISK_TEST_GIGABYTE, $totalGb * DISK_TEST_GIGABYTE)->run();

    expect($result->passed)->toBeTrue()
        ->and($result->message)->toStartWith('Disk space OK');
})->with([
    'plenty free' => [60, 100],
    'exactly 20% and above 5 GB' => [20, 100],
    'exactly 5 GB on a small disk' => [5, 20],
]);

test('it fails when under 20% of the disk is free', function () {
    $result = diskSpaceCheckWith(19 * DISK_TEST_GIGABYTE, 100 * DISK_TEST_GIGABYTE)->run();

    expect($result->passed)->toBeFalse()
        ->and($result->message)->toBe('Low disk space: 19 GB free (19% of the disk)');
});

test('it fails when under 5 GB is free even though that is over 20% of the disk', function () {
    $result = diskSpaceCheckWith(4 * DISK_TEST_GIGABYTE, 10 * DISK_TEST_GIGABYTE)->run();

    expect($result->passed)->toBeFalse()
        ->and($result->message)->toBe('Low disk space: 4 GB free (40% of the disk)');
});

test('it fails when the disk size cannot be read', function () {
    $result = diskSpaceCheckWith(0, 0)->run();

    expect($result->passed)->toBeFalse()
        ->and($result->message)->toStartWith('Low disk space');
});
