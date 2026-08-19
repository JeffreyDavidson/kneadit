<?php

use App\Enums\Marketing\EmailCampaignStatus;
use App\Filament\Resources\EmailCampaigns\EmailCampaignResource;
use App\Filament\Resources\EmailCampaigns\Pages\ListEmailCampaigns;
use App\Models\Engagement\EmailCampaign;
use App\Models\Staff\User;
use Filament\Actions\CreateAction;
use Filament\Actions\Testing\TestAction;
use Laravel\Pennant\Feature;
use Livewire\Livewire;

beforeEach(function () {
    setUpCentralTest();
    test()->actingAs(User::factory()->owner()->create());
    Feature::define('pro-features', fn () => true);
});

test('can list email campaigns in the table', function () {
    $campaigns = EmailCampaign::factory()->count(3)->create();

    Livewire::test(ListEmailCampaigns::class)
        ->assertCanSeeTableRecords($campaigns);
});

test('can create an email campaign via slide-over', function () {
    Livewire::test(ListEmailCampaigns::class)
        ->callAction(CreateAction::class, data: [
            'subject' => 'Spring Sale Announcement',
            'body' => '<p>Our spring sale starts now!</p>',
        ])
        ->assertHasNoFormErrors();

    test()->assertDatabaseHas(EmailCampaign::class, [
        'subject' => 'Spring Sale Announcement',
    ]);
});

test('create email campaign validates subject is required', function () {
    Livewire::test(ListEmailCampaigns::class)
        ->callAction(CreateAction::class, data: [
            'subject' => null,
            'body' => '<p>Test body</p>',
        ])
        ->assertHasFormErrors(['subject' => 'required']);
});

test('can edit an email campaign via table action', function () {
    $campaign = EmailCampaign::factory()->create();

    Livewire::test(ListEmailCampaigns::class)
        ->callAction(TestAction::make('edit')->table($campaign), data: [
            'subject' => 'Updated Subject Line',
            'body' => $campaign->body,
        ])
        ->assertHasNoFormErrors();

    expect($campaign->refresh()->subject)->toBe('Updated Subject Line');
});

test('can render email campaign table columns', function (string $column) {
    EmailCampaign::factory()->create();

    Livewire::test(ListEmailCampaigns::class)
        ->assertCanRenderTableColumn($column);
})->with(['subject', 'status', 'recipient_count']);

test('can search email campaigns by subject', function () {
    $target = EmailCampaign::factory()->create(['subject' => 'Spring Sale']);
    $other = EmailCampaign::factory()->create(['subject' => 'Holiday Special']);

    Livewire::test(ListEmailCampaigns::class)
        ->searchTable('Spring')
        ->assertCanSeeTableRecords(collect([$target]))
        ->assertCanNotSeeTableRecords(collect([$other]));
});

test('can filter email campaigns by status', function () {
    $draft = EmailCampaign::factory()->draft()->create();
    $sent = EmailCampaign::factory()->sent()->create();

    Livewire::test(ListEmailCampaigns::class)
        ->filterTable('status', EmailCampaignStatus::Draft->value)
        ->assertCanSeeTableRecords(collect([$draft]))
        ->assertCanNotSeeTableRecords(collect([$sent]));
});

test('resource returns globally searchable attributes', function () {
    expect(EmailCampaignResource::getGloballySearchableAttributes())
        ->toBe(['subject']);
});

test('resource returns global search result title', function () {
    $campaign = EmailCampaign::factory()->create(['subject' => 'Big Sale']);

    expect(EmailCampaignResource::getGlobalSearchResultTitle($campaign))
        ->toBe('Big Sale');
});

test('resource returns global search result details', function () {
    $campaign = EmailCampaign::factory()->create([
        'status' => EmailCampaignStatus::Draft,
        'recipient_count' => 42,
    ]);

    $details = EmailCampaignResource::getGlobalSearchResultDetails($campaign);

    expect($details)
        ->toHaveKey('Status')
        ->toHaveKey('Recipients', '42');
});
