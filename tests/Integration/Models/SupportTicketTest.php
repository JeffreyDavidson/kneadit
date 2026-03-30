<?php

use App\Enums\SupportTicketPriority;
use App\Enums\SupportTicketStatus;
use App\Models\SupportReply;
use App\Models\SupportTicket;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

beforeEach(fn () => setUpCentralTest());

test('can create ticket', function () {
    $ticket = SupportTicket::factory()->create([
        'subject' => 'Help needed',
        'body' => 'Something is broken',
        'priority' => 'high',
        'tenant_id' => 'tenant-1',
    ]);

    $found = SupportTicket::query()->where('subject', 'Help needed')->first();
    expect($found)->not->toBeNull()->and($found->priority)->toBe(SupportTicketPriority::High);
});

test('status defaults to open', function () {
    $ticket = SupportTicket::factory()->create([
        'tenant_id' => 't1',
    ]);

    expect($ticket->fresh()->status)->toBe(SupportTicketStatus::Open);
});

test('priority defaults to normal', function () {
    $ticket = SupportTicket::factory()->create([
        'tenant_id' => 't1',
    ]);

    expect($ticket->fresh()->priority)->toBe(SupportTicketPriority::Normal);
});

test('replies relationship', function () {
    $ticket = SupportTicket::factory()->create([
        'tenant_id' => 't1',
    ]);

    SupportReply::query()->create(['ticket_id' => $ticket->id, 'body' => 'Reply 1']);
    SupportReply::query()->create(['ticket_id' => $ticket->id, 'body' => 'Reply 2']);

    expect($ticket->replies)->toHaveCount(2);
});

test('tenant relationship exists', function () {
    $ticket = SupportTicket::factory()->create(['tenant_id' => 't1']);

    expect($ticket->tenant())->toBeInstanceOf(BelongsTo::class);
});
