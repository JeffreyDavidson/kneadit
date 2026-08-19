<?php

use App\Enums\Marketing\EmailTemplateType;
use App\Filament\Pages\Settings\ManageEmailTemplates;
use App\Models\Marketing\EmailTemplate;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    test()->page = new ManageEmailTemplates;
});

test('getTemplateData returns all email template types', function () {
    $data = testFixture('page', ManageEmailTemplates::class)->getTemplateData();

    expect($data)->toHaveSameSize(EmailTemplateType::cases());
});

test('getTemplateData marks uncustomized templates as default', function () {
    $data = testFixture('page', ManageEmailTemplates::class)->getTemplateData();

    $statuses = array_column($data, 'status');

    expect($statuses)->each->toBe('Default');
});

test('getTemplateData marks customized templates correctly', function () {
    EmailTemplate::factory()->create([
        'email_type' => EmailTemplateType::OrderPlaced,
        'subject' => 'Custom',
        'body' => 'Custom body',
    ]);

    $data = testFixture('page', ManageEmailTemplates::class)->getTemplateData();
    $orderPlaced = collect($data)->firstWhere('type', 'order_placed');
    throw_unless(is_array($orderPlaced), RuntimeException::class, 'Expected order placed template.');

    expect($orderPlaced['status'])->toBe('Customized');
});

test('resetTemplate deletes custom template', function () {
    EmailTemplate::factory()->create([
        'email_type' => EmailTemplateType::OrderPlaced,
        'subject' => 'Custom',
        'body' => 'Custom body',
    ]);

    testFixture('page', ManageEmailTemplates::class)->resetTemplate('order_placed');

    expect(EmailTemplate::query()->where('email_type', EmailTemplateType::OrderPlaced)->exists())->toBeFalse();
});

test('each template type includes placeholders', function () {
    $data = testFixture('page', ManageEmailTemplates::class)->getTemplateData();

    foreach ($data as $template) {
        expect($template['placeholders'])->toBeArray()->not->toBeEmpty();
    }
});
