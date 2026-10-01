<?php

use App\Models\Engagement\EmailCampaign;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Route;

beforeEach(fn () => setUpCentralTest());

test('the bakery admin panel has no resource for platform email campaigns', function () {
    $models = collect(Filament::getPanel('admin')->getResources())
        ->map(fn (string $resource): ?string => $resource::getModel());

    expect($models)->not->toContain(EmailCampaign::class)
        ->and(Route::has('filament.admin.resources.email-campaigns.index'))->toBeFalse();
});

test('the central panel keeps the platform email campaigns resource', function () {
    $models = collect(Filament::getPanel('central')->getResources())
        ->map(fn (string $resource): ?string => $resource::getModel());

    expect($models)->toContain(EmailCampaign::class);
});
