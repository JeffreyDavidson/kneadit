<?php

use App\Enums\Marketing\EmailTemplateType;
use App\Filament\Pages\Settings\ManageEmailTemplates;
use App\Models\Marketing\EmailTemplate;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    test()->page = new ManageEmailTemplates;
});

test('getTemplateData returns all email template types', function () {
    $data = test()->page->getTemplateData();

    expect($data)->toHaveCount(count(EmailTemplateType::cases()));
});

test('getTemplateData marks uncustomized templates as default', function () {
    $data = test()->page->getTemplateData();

    $statuses = array_column($data, 'status');

    expect($statuses)->each->toBe('Default');
});

test('getTemplateData marks customized templates correctly', function () {
    EmailTemplate::factory()->create([
        'email_type' => EmailTemplateType::OrderPlaced,
        'subject' => 'Custom',
        'body' => 'Custom body',
    ]);

    $data = test()->page->getTemplateData();
    $orderPlaced = collect($data)->firstWhere('type', 'order_placed');

    expect($orderPlaced['status'])->toBe('Customized');
});

test('resetTemplate deletes custom template', function () {
    EmailTemplate::factory()->create([
        'email_type' => EmailTemplateType::OrderPlaced,
        'subject' => 'Custom',
        'body' => 'Custom body',
    ]);

    test()->page->resetTemplate('order_placed');

    expect(EmailTemplate::query()->where('email_type', EmailTemplateType::OrderPlaced)->exists())->toBeFalse();
});

test('each template type includes placeholders', function () {
    $data = test()->page->getTemplateData();

    foreach ($data as $template) {
        expect($template['placeholders'])->toBeArray()->not->toBeEmpty();
    }
});
