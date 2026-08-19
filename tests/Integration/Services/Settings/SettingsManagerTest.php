<?php

use App\Models\Platform\Setting;
use App\Services\Settings\SettingsManager;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    resolve(SettingsManager::class)->flushCache();
});

test('setMany saves multiple settings in a single transaction', function () {
    $manager = resolve(SettingsManager::class);

    $manager->setMany([
        'store_name' => 'Test Bakery',
        'store_email' => 'test@example.com',
        'store_phone' => '555-0100',
    ]);

    $manager->flushCache();

    expect($manager->get('store_name'))->toBe('Test Bakery')
        ->and($manager->get('store_email'))->toBe('test@example.com')
        ->and($manager->get('store_phone'))->toBe('555-0100');
});

test('setMany overwrites existing settings', function () {
    $manager = resolve(SettingsManager::class);

    $manager->set('store_name', 'Old Name');
    $manager->setMany(['store_name' => 'New Name']);

    $manager->flushCache();

    expect($manager->get('store_name'))->toBe('New Name');
});

test('sensitive tenant settings are encrypted at rest and decrypted when read', function (string $key) {
    $manager = resolve(SettingsManager::class);

    $manager->set($key, 'tenant-secret');

    $stored = Setting::query()->where('key', $key)->value('value');
    $manager->flushCache();

    if (! is_string($stored)) {
        throw new UnexpectedValueException('Expected an encrypted setting string.');
    }

    expect($stored)
        ->not->toBe('tenant-secret')
        ->and(Crypt::decryptString($stored))->toBe('tenant-secret')
        ->and($manager->get($key))->toBe('tenant-secret');
})->with([
    'PayPal client ID' => 'paypal_client_id',
    'PayPal client secret' => 'paypal_client_secret',
    'webhook signing secret' => 'webhook_secret',
]);

test('legacy plaintext credentials remain readable before their migration runs', function () {
    Setting::factory()->create(['key' => 'webhook_secret', 'value' => 'legacy-plaintext-secret']);

    $manager = resolve(SettingsManager::class);
    $manager->flushCache();

    expect($manager->get('webhook_secret'))->toBe('legacy-plaintext-secret');
});

test('credential migration encrypts legacy values without double encryption', function () {
    Setting::factory()->create(['key' => 'paypal_client_secret', 'value' => 'legacy-paypal-secret']);

    $migration = require database_path('migrations/tenant/2026_08_17_000000_encrypt_sensitive_tenant_settings.php');
    throw_unless($migration instanceof Migration, RuntimeException::class, 'Expected a migration instance.');
    $migration->up();

    $stored = Setting::query()->where('key', 'paypal_client_secret')->value('value');

    if (! is_string($stored)) {
        throw new UnexpectedValueException('Expected an encrypted setting string.');
    }

    $migration->up();

    expect(Crypt::decryptString($stored))->toBe('legacy-paypal-secret')
        ->and(Setting::query()->where('key', 'paypal_client_secret')->value('value'))->toBe($stored);
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

    expect($manager->pageContentAll('nonexistent'))->toBeEmpty();
});
