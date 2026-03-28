<?php

use App\Models\Order;
use App\Services\PayPal\InvoiceService;
use App\Services\PayPal\PaymentVerifier;
use App\Services\PayPalService;

test('createAndSendInvoice delegates to invoice service', function () {
    $invoiceService = Mockery::mock(InvoiceService::class);
    $invoiceService->shouldReceive('createAndSend')->once()->andReturn('INV-001');

    $paymentVerifier = Mockery::mock(PaymentVerifier::class);

    $service = new PayPalService($invoiceService, $paymentVerifier);
    $order = Mockery::mock(Order::class);

    expect($service->createAndSendInvoice($order))->toBe('INV-001');
});
