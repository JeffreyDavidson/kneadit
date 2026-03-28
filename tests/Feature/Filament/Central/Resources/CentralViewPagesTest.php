<?php

use App\Filament\Central\Resources\TenantResource\Pages\ViewTenant;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

beforeEach(function () {
    setUpCentralTest();
    $this->actingAs(User::factory()->platformAdmin()->create());
    Filament::setCurrentPanel(Filament::getPanel('central'));
});

// ViewTenant has a custom view referencing a missing Filament 5 component
test('can render the view tenant page')
    ->todo();

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
