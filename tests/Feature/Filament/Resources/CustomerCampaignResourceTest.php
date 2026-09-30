<?php

use App\Filament\Resources\CustomerCampaigns\Pages\ListCustomerCampaigns;
use App\Models\Engagement\CustomerCampaign;
use App\Models\Staff\User;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;

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
