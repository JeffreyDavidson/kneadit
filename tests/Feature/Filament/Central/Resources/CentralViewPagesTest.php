<?php

use App\Filament\Central\Resources\TenantResource\Pages\ViewTenant;
use App\Models\Staff\User;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

beforeEach(function () {
    setUpCentralTest();
    test()->actingAs(User::factory()->platformAdmin()->create());
    Filament::setCurrentPanel(Filament::getPanel('central'));
});

test('can render the view tenant page', function () {
    $tenant = DB::table('tenants')->where('id', 'test-bakery')->first();
    if (! $tenant) {
        DB::table('tenants')->insert([
            'id' => 'test-bakery',
            'name' => 'Test Baker',
            'email' => 'baker@test.com',
            'plan' => 'pro',
            'store_name' => 'Test Bakery',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    Livewire::test(ViewTenant::class, ['record' => 'test-bakery'])
        ->assertOk();
});

test('can render the view ticket page', function () {
    $ticket = DB::table('support_tickets')->insertGetId([
        'subject' => 'Test Ticket',
        'body' => 'Test body',
        'status' => 'open',
        'priority' => 'normal',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    Livewire::test(App\Filament\Central\Resources\SupportTicketResource\Pages\ViewTicket::class, ['record' => $ticket])
        ->assertOk();
});

test('can render the view message page', function () {
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

    $messageId = DB::table('platform_messages')->insertGetId([
        'tenant_id' => 'msg-bakery',
        'sender_type' => 'admin',
        'subject' => 'Test Message',
        'body' => 'Test body content',
        'is_read' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    Livewire::test(App\Filament\Central\Resources\MessageResource\Pages\ViewMessage::class, ['record' => $messageId])
        ->assertOk();
});

test('viewing an unread message marks it as read', function () {
    if (! DB::table('tenants')->where('id', 'read-bakery')->exists()) {
        DB::table('tenants')->insert([
            'id' => 'read-bakery',
            'name' => 'Read Baker',
            'email' => 'read@test.com',
            'plan' => 'pro',
            'store_name' => 'Read Bakery',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    $messageId = DB::table('platform_messages')->insertGetId([
        'tenant_id' => 'read-bakery',
        'sender_type' => 'tenant',
        'subject' => 'Unread Message',
        'body' => 'This should be marked as read',
        'is_read' => false,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    Livewire::test(App\Filament\Central\Resources\MessageResource\Pages\ViewMessage::class, ['record' => $messageId])
        ->assertOk();

    $message = DB::table('platform_messages')->where('id', $messageId)->first();
    expect($message->is_read)->toBeTruthy();
});

test('can add reply to support ticket', function () {
    $ticketId = DB::table('support_tickets')->insertGetId([
        'subject' => 'Need Help',
        'body' => 'Please help me.',
        'status' => 'open',
        'priority' => 'normal',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    Livewire::test(App\Filament\Central\Resources\SupportTicketResource\Pages\ViewTicket::class, ['record' => $ticketId])
        ->set('replyBody', 'We are looking into this.')
        ->call('addReply');

    $reply = DB::table('support_replies')->where('ticket_id', $ticketId)->first();
    expect($reply)
        ->not->toBeNull()
        ->and($reply->body)->toBe('We are looking into this.');
});

test('can update support ticket status', function () {
    $ticketId = DB::table('support_tickets')->insertGetId([
        'subject' => 'Open Ticket',
        'body' => 'Body content',
        'status' => 'open',
        'priority' => 'normal',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    Livewire::test(App\Filament\Central\Resources\SupportTicketResource\Pages\ViewTicket::class, ['record' => $ticketId])
        ->call('updateStatus', 'in_progress');

    $ticket = DB::table('support_tickets')->where('id', $ticketId)->first();
    expect($ticket->status)->toBe('in_progress');
});

test('resolving ticket sets resolved_at timestamp', function () {
    $ticketId = DB::table('support_tickets')->insertGetId([
        'subject' => 'Ticket to Resolve',
        'body' => 'Body content',
        'status' => 'in_progress',
        'priority' => 'normal',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    Livewire::test(App\Filament\Central\Resources\SupportTicketResource\Pages\ViewTicket::class, ['record' => $ticketId])
        ->call('updateStatus', 'resolved');

    $ticket = DB::table('support_tickets')->where('id', $ticketId)->first();
    expect($ticket->status)->toBe('resolved');
});

test('tenant view page returns tenant stats', function () {
    $tenant = DB::table('tenants')->where('id', 'test-bakery')->first();
    if (! $tenant) {
        DB::table('tenants')->insert([
            'id' => 'test-bakery',
            'name' => 'Test Baker',
            'email' => 'baker@test.com',
            'plan' => 'pro',
            'store_name' => 'Test Bakery',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    $component = Livewire::test(ViewTenant::class, ['record' => 'test-bakery']);

    $stats = $component->instance()->getTenantStats();

    expect($stats)
        ->toHaveKeys(['products', 'orders', 'revenue', 'customers']);
});
