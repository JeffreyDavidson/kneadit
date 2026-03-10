<?php

namespace Tests\Unit\Central;

use App\Models\TenantNote;
use Tests\CentralTestCase;

class TenantNoteTest extends CentralTestCase
{
    public function test_can_create_note(): void
    {
        $note = TenantNote::create([
            'tenant_id' => 'tenant-1',
            'body' => 'This tenant needs attention',
            'author' => 'Admin Joe',
        ]);

        $found = TenantNote::where('tenant_id', 'tenant-1')->first();
        $this->assertNotNull($found);
        $this->assertEquals('This tenant needs attention', $found->body);
        $this->assertEquals('Admin Joe', $found->author);
    }
}
