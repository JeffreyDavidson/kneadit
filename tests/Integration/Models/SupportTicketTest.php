<?php

use App\Enums\Platform\SupportTicketPriority;
use App\Enums\Platform\SupportTicketStatus;
use App\Models\Platform\SupportReply;
use App\Models\Platform\SupportTicket;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

beforeEach(fn () => setUpCentralTest());

test('can create ticket', function () {
    $ticket = SupportTicket::factory()->create([
        'subject' => 'Help needed',
        'body' => 'Something is broken',
        'priority' => 'high',
        'tenant_id' => 'tenant-1',
    ]);

    $found = SupportTicket::query()->where('subject', 'Help needed')->firstOrFail();
    expect($found)->not->toBeNull()->and($found->priority)->toBe(SupportTicketPriority::High);
});

test('status defaults to open', function () {
    $ticket = SupportTicket::factory()->create([
        'tenant_id' => 't1',
    ]);

    expect($ticket->refresh()->status)->toBe(SupportTicketStatus::Open);
});

test('priority defaults to normal', function () {
    $ticket = SupportTicket::factory()->create([
        'tenant_id' => 't1',
    ]);

    expect($ticket->refresh()->priority)->toBe(SupportTicketPriority::Normal);
});

test('replies relationship', function () {
    $ticket = SupportTicket::factory()->create([
        'tenant_id' => 't1',
    ]);

    SupportReply::factory()->for($ticket, 'ticket')->create(['body' => 'Reply 1']);
    SupportReply::factory()->for($ticket, 'ticket')->create(['body' => 'Reply 2']);

    expect($ticket->replies)->toHaveCount(2);
});

test('tenant relationship exists', function () {
    $ticket = SupportTicket::factory()->create(['tenant_id' => 't1']);

    expect($ticket->tenant())->toBeInstanceOf(BelongsTo::class);
});
