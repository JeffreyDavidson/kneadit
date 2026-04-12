<?php

use App\Filament\Resources\CateringInquiries\Schemas\CateringInquiryForm;
use App\Filament\Resources\EmailCampaigns\Schemas\EmailCampaignForm;
use App\Filament\Resources\GiftCards\GiftCardResource;
use App\Filament\Resources\GiftCards\Pages\ViewGiftCard;
use App\Filament\Resources\Orders\Schemas\OrderForm;
use App\Filament\Resources\Suppliers\RelationManagers\IngredientsRelationManager;
use Filament\Schemas\Schema;

beforeEach(fn () => setUpTenantTest());

test('catering inquiry form configure returns schema', function () {
    $schema = CateringInquiryForm::configure(Schema::make());

    expect($schema)->toBeInstanceOf(Schema::class);
});

test('catering inquiry form schema has components', function () {
    $schema = CateringInquiryForm::configure(Schema::make());

    expect($schema->getComponents())->not->toBeEmpty();
});

test('tenant email campaign form configure returns schema', function () {
    $schema = EmailCampaignForm::configure(Schema::make());

    expect($schema)->toBeInstanceOf(Schema::class);
});

test('tenant email campaign form schema has components', function () {
    $schema = EmailCampaignForm::configure(Schema::make());

    expect($schema->getComponents())->not->toBeEmpty();
});

test('order form configure returns schema', function () {
    $schema = OrderForm::configure(Schema::make());

    expect($schema)->toBeInstanceOf(Schema::class);
});

test('order form schema has components', function () {
    $schema = OrderForm::configure(Schema::make());

    expect($schema->getComponents())->not->toBeEmpty();
});

test('ingredients relation manager has correct relationship', function () {
    expect(
        (new ReflectionClass(IngredientsRelationManager::class))
            ->getStaticPropertyValue('relationship'),
    )->toBe('ingredients');
});

test('view gift card references gift card resource', function () {
    expect(
        (new ReflectionClass(ViewGiftCard::class))
            ->getStaticPropertyValue('resource'),
    )->toBe(GiftCardResource::class);
});
