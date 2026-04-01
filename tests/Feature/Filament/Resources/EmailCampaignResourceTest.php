<?php

use App\Enums\Marketing\EmailCampaignStatus;
use App\Filament\Resources\EmailCampaigns\Pages\ListEmailCampaigns;
use App\Models\Engagement\EmailCampaign;
use App\Models\Staff\User;
use Laravel\Pennant\Feature;
use Livewire\Livewire;

beforeEach(function () {
    setUpCentralTest();
    $this->actingAs(User::factory()->owner()->create());
    Feature::define('pro-features', fn () => true);
});

test('can filter email campaigns by status', function () {
    $draft = EmailCampaign::factory()->create(['status' => EmailCampaignStatus::Draft]);
    $sent = EmailCampaign::factory()->create(['status' => EmailCampaignStatus::Sent]);

    Livewire::test(ListEmailCampaigns::class)
        ->filterTable('status', EmailCampaignStatus::Draft->value)
        ->assertCanSeeTableRecords(collect([$draft]))
        ->assertCanNotSeeTableRecords(collect([$sent]));
});
