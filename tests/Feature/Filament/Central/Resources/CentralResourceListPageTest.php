<?php

use App\Filament\Central\Resources\AnnouncementResource\Pages\ListAnnouncements;
use App\Filament\Central\Resources\BlogPostResource\Pages\ListBlogPosts;
use App\Filament\Central\Resources\EmailCampaignResource\Pages\ListEmailCampaigns;
use App\Filament\Central\Resources\MessageResource\Pages\ListMessages;
use App\Filament\Central\Resources\ScheduledCheckinResource\Pages\ListScheduledCheckins;
use App\Filament\Central\Resources\SupportTicketResource\Pages\ListTickets;
use App\Filament\Central\Resources\TenantResource\Pages\ListTenants;
use App\Models\User;
use Filament\Facades\Filament;
use Livewire\Livewire;

beforeEach(function () {
    setUpCentralTest();
    $user = User::factory()->platformAdmin()->create();
    $this->actingAs($user);
    Filament::setCurrentPanel(Filament::getPanel('central'));
});

dataset('centralResourceListPages', [
    'Announcements' => [ListAnnouncements::class],
    'BlogPosts' => [ListBlogPosts::class],
    'EmailCampaigns' => [ListEmailCampaigns::class],
    'Messages' => [ListMessages::class],
    'ScheduledCheckins' => [ListScheduledCheckins::class],
    'SupportTickets' => [ListTickets::class],
    'Tenants' => [ListTenants::class],
]);

test('central resource list page can render', function (string $pageClass) {
    Livewire::test($pageClass)
        ->assertOk();
})->with('centralResourceListPages');
