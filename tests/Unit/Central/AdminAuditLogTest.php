<?php

namespace Tests\Unit\Central;

use App\Models\AdminAuditLog;
use Tests\CentralTestCase;

class AdminAuditLogTest extends CentralTestCase
{
    public function test_log_creates_record(): void
    {
        $log = AdminAuditLog::log('tenant.suspend', 'Suspended tenant', 'tenant', 'tenant-1', ['reason' => 'abuse']);

        $found = AdminAuditLog::where('action', 'tenant.suspend')->first();
        $this->assertNotNull($found);
        $this->assertEquals('Suspended tenant', $found->description);
        $this->assertEquals('tenant', $found->target_type);
        $this->assertEquals('tenant-1', $found->target_id);
        $this->assertEquals(['reason' => 'abuse'], $found->metadata);
    }

    public function test_log_works_with_minimal_params(): void
    {
        $log = AdminAuditLog::log('login', 'Admin logged in');

        $this->assertNotNull(AdminAuditLog::where('action', 'login')->first());
        $this->assertNull($log->target_type);
    }

    public function test_scope_for_action(): void
    {
        AdminAuditLog::log('login', 'Logged in');
        AdminAuditLog::log('logout', 'Logged out');

        $this->assertCount(1, AdminAuditLog::forAction('login')->get());
    }

    public function test_scope_for_target(): void
    {
        AdminAuditLog::log('update', 'Updated', 'tenant', 't1');
        AdminAuditLog::log('update', 'Updated', 'user', 'u1');

        $this->assertCount(1, AdminAuditLog::forTarget('tenant', 't1')->get());
        $this->assertCount(1, AdminAuditLog::forTarget('user')->get());
    }

    public function test_scope_recent(): void
    {
        $log = AdminAuditLog::log('test', 'Recent');

        $results = AdminAuditLog::recent()->get();
        $this->assertTrue($results->contains($log));
    }
}
