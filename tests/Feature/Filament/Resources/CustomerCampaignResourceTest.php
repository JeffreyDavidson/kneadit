<?php

use App\Filament\Resources\CustomerCampaigns\Pages\ListCustomerCampaigns;
use App\Models\Engagement\CustomerCampaign;
use App\Models\Staff\User;
use App\Services\Settings\TenantSettings;
use Filament\Actions\CreateAction;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Date;

use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    test()->actingAs(User::factory()->owner()->create());
});

test('can list customer campaigns in the table', function () {
    $campaigns = CustomerCampaign::factory()->count(3)->create();

    livewire(ListCustomerCampaigns::class)
        ->assertCanSeeTableRecords($campaigns);
});

test('owner can edit a draft customer campaign via slide-over', function () {
    $campaign = CustomerCampaign::factory()->create();

    livewire(ListCustomerCampaigns::class)
        ->callAction(TestAction::make('edit')->table($campaign), data: [
            'name' => 'Updated campaign name',
            'target_segment' => 'all',
            'subject' => $campaign->subject,
            'body' => $campaign->body,
        ])
        ->assertHasNoFormErrors();

    expect($campaign->fresh()->name)->toBe('Updated campaign name');
});

test('the campaign send time is entered and shown in the bakery timezone', function () {
    app()->instance(TenantSettings::class, makeTenantSettings(orders: makeOrderSettings(['timezone' => 'America/New_York'])));

    livewire(ListCustomerCampaigns::class)
        ->callAction(CreateAction::class, data: [
            'name' => 'Holiday push',
            'target_segment' => 'all',
            'subject' => 'Order early',
            'body' => 'Ovens are filling up.',
            'scheduled_at' => '2030-10-15 09:00:00',
        ])
        ->assertHasNoFormErrors();
    $campaign = CustomerCampaign::query()->where('name', 'Holiday push')->firstOrFail();

    // 9:00 in New York (EDT) is 13:00 UTC, which is what the scheduler compares against.
    expect($campaign->scheduled_at->format('Y-m-d H:i'))->toBe('2030-10-15 13:00');

    livewire(ListCustomerCampaigns::class)
        ->mountAction(TestAction::make('edit')->table($campaign))
        ->assertSet('mountedActions.0.data.scheduled_at', '2030-10-15 09:00');
});

test('the earliest send time follows the bakery clock, not the app clock', function (string $sendAt, bool $accepted) {
    app()->instance(TenantSettings::class, makeTenantSettings(orders: makeOrderSettings(['timezone' => 'Pacific/Auckland'])));
    // 20:00 UTC on the 14th is already 09:00 on the 15th in Auckland.
    Date::setTestNow('2030-10-14 20:00:00');

    $test = livewire(ListCustomerCampaigns::class)
        ->callAction(CreateAction::class, data: [
            'name' => 'Late push',
            'target_segment' => 'all',
            'subject' => 'Order early',
            'body' => 'Ovens are filling up.',
            'scheduled_at' => $sendAt,
        ]);

    $accepted ? $test->assertHasNoFormErrors() : $test->assertHasFormErrors(['scheduled_at']);
})->with([
    'bakery wall time already passed' => ['2030-10-15 08:00:00', false],
    'bakery wall time still ahead' => ['2030-10-15 10:00:00', true],
]);
