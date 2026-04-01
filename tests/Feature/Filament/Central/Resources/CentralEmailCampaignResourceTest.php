<?php

use App\Filament\Central\Resources\EmailCampaignResource\Pages\ListEmailCampaigns;
use App\Models\Engagement\EmailCampaign;
use App\Models\Staff\User;
use Filament\Facades\Filament;
use Livewire\Livewire;

beforeEach(function () {
    setUpCentralTest();
    $user = User::factory()->platformAdmin()->create();
    $this->actingAs($user);
    Filament::setCurrentPanel(Filament::getPanel('central'));
});

test('can filter central email campaigns by status', function () {
    $draft = EmailCampaign::factory()->create(['status' => 'draft']);
    $sent = EmailCampaign::factory()->create(['status' => 'sent']);

    Livewire::test(ListEmailCampaigns::class)
        ->filterTable('status', 'draft')
        ->assertCanSeeTableRecords(collect([$draft]))
        ->assertCanNotSeeTableRecords(collect([$sent]));
});
