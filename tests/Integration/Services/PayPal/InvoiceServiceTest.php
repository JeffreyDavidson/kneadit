<?php

use App\Models\Order;
use App\Services\PayPal\InvoiceService;
use App\Services\PayPal\TokenManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('creates and sends invoice successfully', function () {
    Http::fake([
        '*/v2/invoicing/invoices' => Http::response(['id' => 'INV-123'], 200),
        '*/v2/invoicing/invoices/INV-123/send' => Http::response([], 200),
    ]);

    $tokenManager = Mockery::mock(TokenManager::class);
    $tokenManager->shouldReceive('getAccessToken')->andReturn('test-token');
    $tokenManager->shouldReceive('getBaseUrl')->andReturn('https://api-m.sandbox.paypal.com');

    $order = Order::factory()->create();

    $service = new InvoiceService($tokenManager);
    $invoiceId = $service->createAndSend($order);

    expect($invoiceId)->toBe('INV-123')
        ->and($order->fresh()->paypal_invoice_id)->toBe('INV-123');
});

test('returns null when no access token', function () {
    $tokenManager = Mockery::mock(TokenManager::class);
    $tokenManager->shouldReceive('getAccessToken')->andReturn(null);

    $order = Order::factory()->create();

    $service = new InvoiceService($tokenManager);
    $result = $service->createAndSend($order);

    expect($result)->toBeNull();
});

test('cancels an invoice successfully', function () {
    Http::fake([
        '*/v2/invoicing/invoices/INV-456/cancel' => Http::response([], 200),
    ]);

    $tokenManager = Mockery::mock(TokenManager::class);
    $tokenManager->shouldReceive('getAccessToken')->andReturn('test-token');
    $tokenManager->shouldReceive('getBaseUrl')->andReturn('https://api-m.sandbox.paypal.com');

    $service = new InvoiceService($tokenManager);
    $result = $service->cancel('INV-456');

    expect($result)->toBeTrue();
});
