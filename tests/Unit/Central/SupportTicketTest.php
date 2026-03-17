<?php

use App\Models\SupportReply;
use App\Models\SupportTicket;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
beforeEach(fn () => setUpCentralTest());

test('can create ticket', function () {
    $ticket = SupportTicket::create([
        'subject' => 'Help needed',
        'body' => 'Something is broken',
        'status' => 'open',
        'priority' => 'high',
        'tenant_id' => 'tenant-1',
    ]);

    $found = SupportTicket::where('subject', 'Help needed')->first();
    expect($found)->not->toBeNull();
    expect($found->priority)->toBe('high');
});

test('status defaults to open', function () {
    $ticket = SupportTicket::create([
        'subject' => 'Test',
        'body' => 'Body',
        'tenant_id' => 't1',
    ]);

    expect($ticket->fresh()->status)->toBe('open');
});

test('priority defaults to normal', function () {
    $ticket = SupportTicket::create([
        'subject' => 'Test',
        'body' => 'Body',
        'tenant_id' => 't1',
    ]);

    expect($ticket->fresh()->priority)->toBe('normal');
});

test('replies relationship', function () {
    $ticket = SupportTicket::create([
        'subject' => 'Test',
        'body' => 'Body',
        'tenant_id' => 't1',
    ]);

    SupportReply::create(['ticket_id' => $ticket->id, 'body' => 'Reply 1']);
    SupportReply::create(['ticket_id' => $ticket->id, 'body' => 'Reply 2']);

    expect($ticket->replies)->toHaveCount(2);
});

test('tenant relationship exists', function () {
    $ticket = SupportTicket::create(['subject' => 'T', 'body' => 'B', 'tenant_id' => 't1']);

    expect($ticket->tenant())->toBeInstanceOf(BelongsTo::class);
});
