<?php

namespace Tests\Unit\Central;

use App\Models\PlatformMessage;
use Tests\CentralTestCase;

class PlatformMessageTest extends CentralTestCase
{
    public function test_can_create_message(): void
    {
        $msg = PlatformMessage::create([
            'tenant_id' => 't1',
            'sender_type' => 'admin',
            'subject' => 'Welcome',
            'body' => 'Hello there',
        ]);

        $this->assertNotNull(PlatformMessage::where('subject', 'Welcome')->first());
    }

    public function test_scope_unread(): void
    {
        PlatformMessage::create(['tenant_id' => 't1', 'sender_type' => 'admin', 'subject' => 'S1', 'body' => 'B', 'is_read' => false]);
        PlatformMessage::create(['tenant_id' => 't1', 'sender_type' => 'admin', 'subject' => 'S2', 'body' => 'B', 'is_read' => true]);

        $this->assertCount(1, PlatformMessage::unread()->get());
    }

    public function test_scope_from_admin(): void
    {
        PlatformMessage::create(['tenant_id' => 't1', 'sender_type' => 'admin', 'subject' => 'S', 'body' => 'B']);
        PlatformMessage::create(['tenant_id' => 't1', 'sender_type' => 'tenant', 'subject' => 'S', 'body' => 'B']);

        $this->assertCount(1, PlatformMessage::fromAdmin()->get());
    }

    public function test_scope_from_tenant(): void
    {
        PlatformMessage::create(['tenant_id' => 't1', 'sender_type' => 'tenant', 'subject' => 'S', 'body' => 'B']);
        PlatformMessage::create(['tenant_id' => 't1', 'sender_type' => 'admin', 'subject' => 'S', 'body' => 'B']);

        $this->assertCount(1, PlatformMessage::fromTenant()->get());
    }

    public function test_scope_top_level(): void
    {
        $parent = PlatformMessage::create(['tenant_id' => 't1', 'sender_type' => 'admin', 'subject' => 'S', 'body' => 'B']);
        PlatformMessage::create(['tenant_id' => 't1', 'sender_type' => 'admin', 'subject' => 'S', 'body' => 'Reply', 'parent_id' => $parent->id]);

        $this->assertCount(1, PlatformMessage::topLevel()->get());
    }

    public function test_replies_relationship(): void
    {
        $parent = PlatformMessage::create(['tenant_id' => 't1', 'sender_type' => 'admin', 'subject' => 'S', 'body' => 'B']);
        PlatformMessage::create(['tenant_id' => 't1', 'sender_type' => 'tenant', 'subject' => 'S', 'body' => 'Reply', 'parent_id' => $parent->id]);

        $this->assertCount(1, $parent->replies);
    }

    public function test_parent_relationship(): void
    {
        $parent = PlatformMessage::create(['tenant_id' => 't1', 'sender_type' => 'admin', 'subject' => 'S', 'body' => 'B']);
        $reply = PlatformMessage::create(['tenant_id' => 't1', 'sender_type' => 'tenant', 'subject' => 'S', 'body' => 'Reply', 'parent_id' => $parent->id]);

        $this->assertEquals($parent->id, $reply->parent->id);
    }
}
