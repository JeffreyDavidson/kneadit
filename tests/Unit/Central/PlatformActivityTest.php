<?php

namespace Tests\Unit\Central;

use App\Models\PlatformActivity;
use Tests\CentralTestCase;

class PlatformActivityTest extends CentralTestCase
{
    public function test_log_creates_record(): void
    {
        $activity = PlatformActivity::log('tenant.created', 'tenant-1', 'A new tenant was created', ['plan' => 'pro']);

        $found = PlatformActivity::where('event', 'tenant.created')->first();
        $this->assertNotNull($found);
        $this->assertEquals('tenant-1', $found->tenant_id);
        $this->assertEquals('A new tenant was created', $found->description);
        $this->assertEquals(['plan' => 'pro'], $found->metadata);
    }

    public function test_log_works_with_null_tenant_id(): void
    {
        $activity = PlatformActivity::log('system.update', null, 'System updated');

        $this->assertNull($activity->tenant_id);
        $found = PlatformActivity::where('event', 'system.update')->first();
        $this->assertNotNull($found);
        $this->assertNull($found->tenant_id);
    }

    public function test_metadata_is_cast_to_array(): void
    {
        $activity = PlatformActivity::log('test', 't1', 'desc', ['key' => 'value']);
        $activity->refresh();

        $this->assertIsArray($activity->metadata);
        $this->assertEquals('value', $activity->metadata['key']);
    }

    public function test_tenant_relationship_exists(): void
    {
        $activity = PlatformActivity::log('test', null, 'desc');

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $activity->tenant());
    }
}
