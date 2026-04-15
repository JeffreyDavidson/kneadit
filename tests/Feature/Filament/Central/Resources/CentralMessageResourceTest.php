<?php

use App\Filament\Central\Resources\MessageResource;
use App\Filament\Central\Resources\MessageResource\Pages\ListMessages;
use App\Filament\Central\Resources\MessageResource\Pages\ViewMessage;
use App\Models\Staff\User;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

beforeEach(function () {
    setUpCentralTest();
    test()->actingAs(User::factory()->platformAdmin()->create());
    Filament::setCurrentPanel(Filament::getPanel('central'));
});

function ensureMessageTenant(): void
{
    if (! DB::table('tenants')->where('id', 'msg-bakery')->exists()) {
        DB::table('tenants')->insert([
            'id' => 'msg-bakery',
            'name' => 'Msg Baker',
            'email' => 'msg@test.com',
            'plan' => 'pro',
            'store_name' => 'Msg Bakery',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}

function createTestMessage(array $overrides = []): int
{
    ensureMessageTenant();

    return DB::table('platform_messages')->insertGetId(array_merge([
        'tenant_id' => 'msg-bakery',
        'sender_type' => 'admin',
        'subject' => 'Test Message',
        'body' => 'Test body content',
        'is_read' => false,
        'created_at' => now(),
        'updated_at' => now(),
    ], $overrides));
}

test('can render the list messages page', function () {
    Livewire::test(ListMessages::class)
        ->assertOk();
});

test('navigation badge shows unread tenant message count', function () {
    createTestMessage(['is_read' => false, 'sender_type' => 'tenant', 'subject' => 'Msg 1']);
    createTestMessage(['is_read' => false, 'sender_type' => 'tenant', 'subject' => 'Msg 2']);
    createTestMessage(['is_read' => true, 'sender_type' => 'tenant', 'subject' => 'Msg 3']);
    createTestMessage(['is_read' => false, 'sender_type' => 'admin', 'subject' => 'Msg 4']);

    expect(MessageResource::getNavigationBadge())
        ->toBe('2');
});

test('navigation badge returns null when no unread tenant messages', function () {
    createTestMessage(['is_read' => true, 'sender_type' => 'tenant', 'subject' => 'Read Msg']);
    createTestMessage(['is_read' => false, 'sender_type' => 'admin', 'subject' => 'Admin Msg']);

    expect(MessageResource::getNavigationBadge())
        ->toBeNull();
});

test('navigation badge color is warning', function () {
    expect(MessageResource::getNavigationBadgeColor())
        ->toBe('warning');
});

test('can render the view message page for admin message', function () {
    $messageId = createTestMessage([
        'sender_type' => 'admin',
        'subject' => 'Admin Sent Message',
        'is_read' => true,
    ]);

    Livewire::test(ViewMessage::class, ['record' => $messageId])
        ->assertOk();
});

test('view message page displays title from subject', function () {
    $messageId = createTestMessage([
        'sender_type' => 'admin',
        'subject' => 'Important Question',
        'is_read' => true,
    ]);

    $component = Livewire::test(ViewMessage::class, ['record' => $messageId]);

    expect($component->instance()->getTitle())
        ->toBe('Important Question');
});

test('view message page returns thread replies', function () {
    $messageId = createTestMessage([
        'sender_type' => 'admin',
        'subject' => 'Thread Start',
        'is_read' => true,
    ]);

    DB::table('platform_messages')->insert([
        'tenant_id' => 'msg-bakery',
        'sender_type' => 'admin',
        'subject' => 'Re: Thread Start',
        'body' => 'Reply body',
        'parent_id' => $messageId,
        'is_read' => false,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $component = Livewire::test(ViewMessage::class, ['record' => $messageId]);
    $thread = $component->instance()->getThread();

    expect($thread)->toHaveCount(1);
});

test('can send reply from view message page', function () {
    $messageId = createTestMessage([
        'sender_type' => 'admin',
        'subject' => 'Need Help',
        'is_read' => true,
    ]);

    Livewire::test(ViewMessage::class, ['record' => $messageId])
        ->set('replyBody', 'We can help with that!')
        ->call('sendReply');

    $reply = DB::table('platform_messages')
        ->where('parent_id', $messageId)
        ->first();

    expect($reply)
        ->not->toBeNull()
        ->and($reply->body)->toBe('We can help with that!')
        ->and($reply->sender_type)->toBe('admin');
});

test('viewing an unread message marks it as read', function () {
    $messageId = createTestMessage([
        'sender_type' => 'admin',
        'subject' => 'Unread Msg',
        'is_read' => false,
    ]);

    Livewire::test(ViewMessage::class, ['record' => $messageId])
        ->assertOk();

    $message = DB::table('platform_messages')->where('id', $messageId)->first();
    expect((bool) $message->is_read)->toBeTrue();
});
