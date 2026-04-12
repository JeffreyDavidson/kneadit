<?php

use App\Models\Platform\Setting;
use App\Services\Settings\SettingsManager;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    resolve(SettingsManager::class)->flushCache();
});

test('pageContent returns value for nested key', function () {
    $manager = resolve(SettingsManager::class);

    Setting::factory()->create([
        'key' => 'page_content',
        'value' => json_encode([
            'home' => ['title' => 'Welcome Home', 'subtitle' => 'Fresh baked'],
            'about' => ['title' => 'About Us'],
        ]),
    ]);

    $manager->flushCache();

    expect($manager->pageContent('home', 'title'))->toBe('Welcome Home')
        ->and($manager->pageContent('home', 'subtitle'))->toBe('Fresh baked')
        ->and($manager->pageContent('about', 'title'))->toBe('About Us');
});

test('pageContent returns default for missing page', function () {
    $manager = resolve(SettingsManager::class);

    Setting::factory()->create([
        'key' => 'page_content',
        'value' => json_encode(['home' => ['title' => 'Welcome']]),
    ]);

    $manager->flushCache();

    expect($manager->pageContent('missing', 'title', 'Default Title'))->toBe('Default Title');
});

test('pageContent returns default for missing key within page', function () {
    $manager = resolve(SettingsManager::class);

    Setting::factory()->create([
        'key' => 'page_content',
        'value' => json_encode(['home' => ['title' => 'Welcome']]),
    ]);

    $manager->flushCache();

    expect($manager->pageContent('home', 'nonexistent', 'fallback'))->toBe('fallback');
});

test('pageContentAll returns all content for a page', function () {
    $manager = resolve(SettingsManager::class);

    Setting::factory()->create([
        'key' => 'page_content',
        'value' => json_encode([
            'home' => ['title' => 'Welcome', 'cta' => 'Order Now'],
        ]),
    ]);

    $manager->flushCache();

    expect($manager->pageContentAll('home'))->toBe(['title' => 'Welcome', 'cta' => 'Order Now']);
});

test('pageContentAll returns empty array for missing page', function () {
    $manager = resolve(SettingsManager::class);

    expect($manager->pageContentAll('nonexistent'))->toBe([]);
});
