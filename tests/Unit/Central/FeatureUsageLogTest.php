<?php

namespace Tests\Unit\Central;

use App\Models\FeatureUsageLog;
use Tests\CentralTestCase;

class FeatureUsageLogTest extends CentralTestCase
{
    public function test_track_creates_new_record(): void
    {
        $log = FeatureUsageLog::track('tenant-1', 'recipe_import');

        $found = FeatureUsageLog::where('tenant_id', 'tenant-1')->where('feature', 'recipe_import')->first();
        $this->assertNotNull($found);
        $this->assertEquals(1, $found->usage_count);
    }

    public function test_track_increments_existing_record(): void
    {
        FeatureUsageLog::track('tenant-1', 'recipe_import');
        FeatureUsageLog::track('tenant-1', 'recipe_import');

        $log = FeatureUsageLog::where('tenant_id', 'tenant-1')
            ->where('feature', 'recipe_import')
            ->first();

        $this->assertEquals(2, $log->usage_count);
        $this->assertCount(1, FeatureUsageLog::where('tenant_id', 'tenant-1')->where('feature', 'recipe_import')->get());
    }
}
