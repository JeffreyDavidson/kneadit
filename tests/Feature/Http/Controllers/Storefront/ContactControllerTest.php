<?php

use App\Models\Platform\Setting;
use App\Services\Settings\SettingsManager;
use App\Services\Settings\TenantSettings;

use function Pest\Laravel\withoutMiddleware;

beforeEach(fn () => setUpTenantTest());

test('contact controller passes settings and content to view', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->get(route('contact.show', [], false));

    $response->assertOk()
        ->assertViewHas('settings')
        ->assertViewHas('content');
});

test('show returns the contact view with tenant settings', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->get(route('contact.show', [], false));

    $response->assertOk()
        ->assertViewIs('storefront.contact')
        ->assertViewHas('settings', fn (TenantSettings $s) => $s->storeName === 'Our Bakery');
});

test('contact page loads', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->get(route('contact.show', [], false));

    $response->assertOk();
});

test('contact form submission works with valid data', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->post(route('contact.store', [], false), [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'subject' => 'Question about orders',
            'message' => 'Do you offer gluten-free options?',
        ]);

    $response->assertRedirect();
    test()->assertDatabaseHas('contact_messages', [
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'subject' => 'Question about orders',
    ]);
});

test('contact form success message can be customized via page content', function () {
    Setting::factory()->create([
        'key' => 'page_content',
        'value' => json_encode([
            'contact' => ['flash_success' => 'Got it — talk soon!'],
        ]),
    ]);
    resolve(SettingsManager::class)->flushCache();

    $response = withoutMiddleware(tenantMiddleware())
        ->post(route('contact.store', [], false), [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'subject' => 'Hi',
            'message' => 'Just saying hello.',
        ]);

    $response->assertRedirect()
        ->assertSessionHas('success', 'Got it — talk soon!');
});

test('contact form validation rejects missing name', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->post(route('contact.store', [], false), [
            'email' => 'jane@example.com',
            'subject' => 'Test',
            'message' => 'Test message',
        ]);

    $response->assertSessionHasErrors('name');
});

test('contact form validation rejects missing email', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->post(route('contact.store', [], false), [
            'name' => 'Jane',
            'subject' => 'Test',
            'message' => 'Test message',
        ]);

    $response->assertSessionHasErrors('email');
});

test('contact form validation rejects missing subject', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->post(route('contact.store', [], false), [
            'name' => 'Jane',
            'email' => 'jane@example.com',
            'message' => 'Test message',
        ]);

    $response->assertSessionHasErrors('subject');
});

test('contact form validation rejects missing message', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->post(route('contact.store', [], false), [
            'name' => 'Jane',
            'email' => 'jane@example.com',
            'subject' => 'Test',
        ]);

    $response->assertSessionHasErrors('message');
});
