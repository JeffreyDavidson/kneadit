<?php

use App\Filament\Central\Resources\EmailCampaignResource\Pages\ListEmailCampaigns;
use App\Models\Engagement\EmailCampaign;
use App\Models\Staff\User;
use Filament\Facades\Filament;

use function Pest\Livewire\livewire;

beforeEach(function () {
    setUpCentralTest();
    $user = User::factory()->platformAdmin()->create();
    $this->actingAs($user);
    Filament::setCurrentPanel(Filament::getPanel('central'));
});

test('can filter central email campaigns by status', function () {
    $draft = EmailCampaign::factory()->draft()->create();
    $sent = EmailCampaign::factory()->sent()->create();

    livewire(ListEmailCampaigns::class)
        ->filterTable('status', 'draft')
        ->assertCanSeeTableRecords(collect([$draft]))
        ->assertCanNotSeeTableRecords(collect([$sent]));
});
