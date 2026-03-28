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
