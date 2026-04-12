<?php

use App\Filament\Central\Resources\SupportTicketResource;
use App\Models\Staff\User;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    setUpCentralTest();
    $this->actingAs(User::factory()->platformAdmin()->create());
    Filament::setCurrentPanel(Filament::getPanel('central'));
});

test('navigation badge shows open ticket count', function () {
    DB::table('support_tickets')->insert([
        ['subject' => 'Open 1', 'body' => 'b', 'status' => 'open', 'priority' => 'normal', 'created_at' => now(), 'updated_at' => now()],
        ['subject' => 'Open 2', 'body' => 'b', 'status' => 'open', 'priority' => 'normal', 'created_at' => now(), 'updated_at' => now()],
        ['subject' => 'Resolved', 'body' => 'b', 'status' => 'resolved', 'priority' => 'normal', 'created_at' => now(), 'updated_at' => now()],
    ]);

    expect(SupportTicketResource::getNavigationBadge())
        ->toBe('2');
});

test('navigation badge returns null when no open tickets', function () {
    DB::table('support_tickets')->insert([
        'subject' => 'Resolved', 'body' => 'b', 'status' => 'resolved', 'priority' => 'normal',
        'created_at' => now(), 'updated_at' => now(),
    ]);

    expect(SupportTicketResource::getNavigationBadge())
        ->toBeNull();
});

test('navigation badge color is danger', function () {
    expect(SupportTicketResource::getNavigationBadgeColor())
        ->toBe('danger');
});

test('resource returns globally searchable attributes', function () {
    expect(SupportTicketResource::getGloballySearchableAttributes())
        ->toBe(['subject']);
});
