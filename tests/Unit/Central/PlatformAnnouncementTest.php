<?php

namespace Tests\Unit\Central;

use App\Models\PlatformAnnouncement;
use Tests\CentralTestCase;

class PlatformAnnouncementTest extends CentralTestCase
{
    public function test_can_create_announcement(): void
    {
        $ann = PlatformAnnouncement::create([
            'title' => 'Maintenance',
            'body' => 'Scheduled downtime',
            'type' => 'warning',
            'target_plans' => ['pro', 'enterprise'],
            'is_active' => true,
        ]);

        $this->assertNotNull(PlatformAnnouncement::where('title', 'Maintenance')->first());
    }

    public function test_target_plans_is_cast_to_array(): void
    {
        $ann = PlatformAnnouncement::create([
            'title' => 'T',
            'body' => 'B',
            'target_plans' => ['free', 'pro'],
        ]);

        $ann->refresh();
        $this->assertIsArray($ann->target_plans);
        $this->assertEquals(['free', 'pro'], $ann->target_plans);
    }

    public function test_is_active_defaults_to_true(): void
    {
        $ann = PlatformAnnouncement::create(['title' => 'T', 'body' => 'B']);

        $this->assertTrue($ann->fresh()->is_active);
    }
}
