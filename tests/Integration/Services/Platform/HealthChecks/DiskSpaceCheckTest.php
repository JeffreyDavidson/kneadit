<?php

use App\Services\Platform\HealthChecks\DiskSpaceCheck;

test('it reports the free disk space of the application volume in gigabytes', function () {
    $freeGb = round((disk_free_space(base_path()) ?: 0) / 1073741824, 1);

    $result = (new DiskSpaceCheck)->run();

    expect($result->message)->toContain("{$freeGb} GB free");
});

test('it passes or fails according to the one gigabyte minimum', function () {
    $freeGb = round((disk_free_space(base_path()) ?: 0) / 1073741824, 1);

    $result = (new DiskSpaceCheck)->run();

    expect($result->passed)->toBe($freeGb >= 1)
        ->and($result->message)->toStartWith($freeGb >= 1 ? 'Disk space OK' : 'Low disk space');
});
