<?php

use App\Filament\Central\Resources\AnnouncementResource\Schemas\AnnouncementForm;
use App\Filament\Central\Resources\BlogPostResource\Pages\EditBlogPost;
use App\Filament\Central\Resources\BlogPostResource\Schemas\BlogPostForm;
use App\Filament\Central\Resources\EmailCampaignResource\Schemas\EmailCampaignForm;
use App\Filament\Central\Resources\ScheduledCheckinResource\Schemas\ScheduledCheckinForm;
use App\Filament\Central\Resources\TenantResource\RelationManagers\NotesRelationManager;
use App\Filament\Central\Resources\TenantResource\Schemas\TenantForm;
use Filament\Schemas\Schema;

beforeEach(fn () => setUpCentralTest());

test('announcement form configure returns schema', function () {
    $schema = AnnouncementForm::configure(Schema::make());

    expect($schema)->toBeInstanceOf(Schema::class);
});

test('announcement form schema has components', function () {
    $schema = AnnouncementForm::configure(Schema::make());

    expect($schema->getComponents())->not->toBeEmpty();
});

test('blog post form configure returns schema', function () {
    $schema = BlogPostForm::configure(Schema::make());

    expect($schema)->toBeInstanceOf(Schema::class);
});

test('blog post form schema has components', function () {
    $schema = BlogPostForm::configure(Schema::make());

    expect($schema->getComponents())->not->toBeEmpty();
});

test('central email campaign form configure returns schema', function () {
    $schema = EmailCampaignForm::configure(Schema::make());

    expect($schema)->toBeInstanceOf(Schema::class);
});

test('central email campaign form schema has components', function () {
    $schema = EmailCampaignForm::configure(Schema::make());

    expect($schema->getComponents())->not->toBeEmpty();
});

test('scheduled checkin form configure returns schema', function () {
    $schema = ScheduledCheckinForm::configure(Schema::make());

    expect($schema)->toBeInstanceOf(Schema::class);
});

test('scheduled checkin form schema has components', function () {
    $schema = ScheduledCheckinForm::configure(Schema::make());

    expect($schema->getComponents())->not->toBeEmpty();
});

test('tenant form configure returns schema', function () {
    $schema = TenantForm::configure(Schema::make());

    expect($schema)->toBeInstanceOf(Schema::class);
});

test('tenant form schema has components', function () {
    $schema = TenantForm::configure(Schema::make());

    expect($schema->getComponents())->not->toBeEmpty();
});

test('notes relation manager has correct relationship', function () {
    expect(
        (new ReflectionClass(NotesRelationManager::class))
            ->getStaticPropertyValue('relationship'),
    )->toBe('notes');
});

test('edit blog post references blog post resource', function () {
    expect(
        (new ReflectionClass(EditBlogPost::class))
            ->getStaticPropertyValue('resource'),
    )->toBe(App\Filament\Central\Resources\BlogPostResource::class);
});
