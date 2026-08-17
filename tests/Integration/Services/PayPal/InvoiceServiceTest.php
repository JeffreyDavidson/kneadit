<?php

use App\Models\Orders\Order;
use App\Services\PayPal\InvoicePayloadBuilder;
use App\Services\PayPal\InvoiceService;
use App\Services\PayPal\TokenManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

function invoiceServiceTokenManager(?string $accessToken): TokenManager
{
    return new class($accessToken) extends TokenManager {
        public function __construct(private ?string $accessToken) {}

        public function getAccessToken(): ?string
        {
            return $this->accessToken;
        }

        public function getBaseUrl(): string
        {
            return 'https://api-m.sandbox.paypal.com';
        }
    };
}

function invoiceServicePayloadBuilder(): InvoicePayloadBuilder
{
    return new class extends InvoicePayloadBuilder {
        public function __construct() {}

        public function build(Order $order): array
        {
            return ['detail' => [], 'items' => []];
        }
    };
}

test('creates and sends invoice successfully', function () {
    Http::preventStrayRequests();
    Http::fake([
        '*/v2/invoicing/invoices' => Http::response(['id' => 'INV-123'], 200),
        '*/v2/invoicing/invoices/INV-123/send' => Http::response([], 200),
    ]);

    $tokenManager = invoiceServiceTokenManager('test-token');

    $order = Order::factory()->create();

    $service = new InvoiceService($tokenManager, invoiceServicePayloadBuilder());
    $invoiceId = $service->createAndSend($order);

    expect($invoiceId)->toBe('INV-123')
        ->and($order->refresh()->paypal_invoice_id)->toBe('INV-123');
});

test('returns null when no access token', function () {
    $tokenManager = invoiceServiceTokenManager(null);

    $order = Order::factory()->create();

    $service = new InvoiceService($tokenManager, invoiceServicePayloadBuilder());
    $result = $service->createAndSend($order);

    expect($result)->toBeNull();
});

test('cancels an invoice successfully', function () {
    Http::preventStrayRequests();
    Http::fake([
        '*/v2/invoicing/invoices/INV-456/cancel' => Http::response([], 200),
    ]);

    $tokenManager = invoiceServiceTokenManager('test-token');

    $service = new InvoiceService($tokenManager, invoiceServicePayloadBuilder());
    $result = $service->cancel('INV-456');

    expect($result)->toBeTrue();
});

test('returns null when create invoice API call fails', function () {
    Http::preventStrayRequests();
    Http::fake([
        '*/v2/invoicing/invoices' => Http::response(['error' => 'Bad request'], 422),
    ]);

    $tokenManager = invoiceServiceTokenManager('test-token');

    $order = Order::factory()->create();

    $service = new InvoiceService($tokenManager, invoiceServicePayloadBuilder());
    $result = $service->createAndSend($order);

    expect($result)->toBeNull();
});

test('returns null when send invoice API call fails', function () {
    Http::preventStrayRequests();
    Http::fake([
        '*/v2/invoicing/invoices' => Http::response(['id' => 'INV-789'], 200),
        '*/v2/invoicing/invoices/INV-789/send' => Http::response(['error' => 'Send failed'], 500),
    ]);

    $tokenManager = invoiceServiceTokenManager('test-token');

    $order = Order::factory()->create();

    $service = new InvoiceService($tokenManager, invoiceServicePayloadBuilder());
    $result = $service->createAndSend($order);

    expect($result)->toBeNull();
});

test('cancel returns false when no access token', function () {
    $tokenManager = invoiceServiceTokenManager(null);

    $service = new InvoiceService($tokenManager, invoiceServicePayloadBuilder());
    $result = $service->cancel('INV-123');

    expect($result)->toBeFalse();
});

test('cancel returns false when API call fails', function () {
    Http::preventStrayRequests();
    Http::fake([
        '*/v2/invoicing/invoices/INV-999/cancel' => Http::response(['error' => 'Not found'], 404),
    ]);

    $tokenManager = invoiceServiceTokenManager('test-token');

    $service = new InvoiceService($tokenManager, invoiceServicePayloadBuilder());
    $result = $service->cancel('INV-999');

    expect($result)->toBeFalse();
});

test('creates invoice with delivery fee and discount', function () {
    Http::preventStrayRequests();
    Http::fake([
        '*/v2/invoicing/invoices' => Http::response(['id' => 'INV-DELIVERY'], 200),
        '*/v2/invoicing/invoices/INV-DELIVERY/send' => Http::response([], 200),
    ]);

    $tokenManager = invoiceServiceTokenManager('test-token');

    $order = Order::factory()->create([
        'delivery_fee' => 5.00,
        'discount_amount' => 2.50,
        'subtotal' => 20.00,
        'total' => 22.50,
    ]);

    $service = new InvoiceService($tokenManager, invoiceServicePayloadBuilder());
    $result = $service->createAndSend($order);

    expect($result)->toBe('INV-DELIVERY');
});
