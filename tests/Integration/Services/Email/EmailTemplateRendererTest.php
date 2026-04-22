<?php

use App\Enums\Marketing\EmailTemplateType;
use App\Models\Marketing\EmailTemplate;
use App\Services\Email\EmailTemplateRenderer;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    test()->renderer = new EmailTemplateRenderer;
});

test('resolve returns null when no custom template exists', function () {
    $result = test()->renderer->resolve(EmailTemplateType::OrderPlaced);

    expect($result)->toBeNull();
});

test('resolve returns custom subject and body when template exists', function () {
    EmailTemplate::factory()->create([
        'email_type' => EmailTemplateType::OrderPlaced,
        'subject' => 'Custom subject for {customer_name}',
        'body' => '<p>Hello {customer_name}, your order #{order_number} is in!</p>',
    ]);

    $result = test()->renderer->resolve(EmailTemplateType::OrderPlaced, [
        'customer_name' => 'Jane',
        'order_number' => '1001',
    ]);

    expect($result)
        ->not->toBeNull()
        ->and($result['subject'])->toBe('Custom subject for Jane')
        ->and($result['body'])->toBe('<p>Hello Jane, your order #1001 is in!</p>');
});

test('replacePlaceholders replaces all tokens', function () {
    $content = 'Hi {customer_name}, order #{order_number} totals {order_total} at {store_name}.';

    $result = test()->renderer->replacePlaceholders($content, [
        'customer_name' => 'Alice',
        'order_number' => '2001',
        'order_total' => '$25.00',
        'store_name' => 'Sweet Treats',
    ]);

    expect($result)->toBe('Hi Alice, order #2001 totals $25.00 at Sweet Treats.');
});

test('replacePlaceholders leaves unknown placeholders untouched', function () {
    $content = 'Hi {customer_name}, code: {unknown_placeholder}';

    $result = test()->renderer->replacePlaceholders($content, [
        'customer_name' => 'Bob',
    ]);

    expect($result)->toBe('Hi Bob, code: {unknown_placeholder}');
});

test('renderDefaultSubject replaces placeholders in the enum default subject', function () {
    $result = test()->renderer->renderDefaultSubject(EmailTemplateType::OrderPlaced, [
        'order_number' => '3001',
        'store_name' => 'My Bakery',
    ]);

    expect($result)->toBe('Order #3001 Received — My Bakery');
});

test('resolve only returns template for the matching type', function () {
    EmailTemplate::factory()->create([
        'email_type' => EmailTemplateType::HappyBirthday,
        'subject' => 'Birthday custom',
        'body' => 'Birthday body',
    ]);

    $result = test()->renderer->resolve(EmailTemplateType::OrderPlaced);

    expect($result)->toBeNull();
});
