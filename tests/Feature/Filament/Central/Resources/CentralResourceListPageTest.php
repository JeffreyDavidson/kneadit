<?php

use App\Filament\Central\Resources\BlogPostResource\Pages\ListBlogPosts;
use App\Filament\Central\Resources\EmailCampaignResource\Pages\ListEmailCampaigns;
use App\Filament\Central\Resources\MessageResource\Pages\ListMessages;
use App\Filament\Central\Resources\ScheduledCheckinResource\Pages\ListScheduledCheckins;
use App\Filament\Central\Resources\SupportTicketResource\Pages\ListTickets;
use App\Filament\Central\Resources\TenantResource\Pages\ListTenants;
use App\Models\Staff\User;
use Filament\Facades\Filament;

use function Pest\Livewire\livewire;

beforeEach(function () {
    setUpCentralTest();
    $user = User::factory()->platformAdmin()->create();
    $this->actingAs($user);
    Filament::setCurrentPanel(Filament::getPanel('central'));
});

dataset('centralResourceListPages', [
    'BlogPosts' => [ListBlogPosts::class],
    'EmailCampaigns' => [ListEmailCampaigns::class],
    'Messages' => [ListMessages::class],
    'ScheduledCheckins' => [ListScheduledCheckins::class],
    'SupportTickets' => [ListTickets::class],
    'Tenants' => [ListTenants::class],
]);

test('central resource list page can render', function (string $pageClass) {
    livewire($pageClass)
        ->assertOk();
})->with('centralResourceListPages');
