<?php

use App\Events\Marketing\PurchaseOrderRequested;
use App\Listeners\Marketing\SendPurchaseOrderEmailListener;
use App\Mail\Orders\PurchaseOrderMail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('it sends purchase order email to the supplier', function () {
    Mail::fake();

    $event = new PurchaseOrderRequested(
        supplierEmail: 'supplier@example.com',
        supplierName: 'Flour Co.',
        storeName: 'Sweet Treats Bakery',
        items: [['name' => 'All-Purpose Flour', 'quantity' => 50, 'unit_price' => 2.50]],
        total: 125.00,
        requestedDate: '2026-04-15',
    );

    $listener = new SendPurchaseOrderEmailListener;
    $listener->handle($event);

    Mail::assertQueued(PurchaseOrderMail::class, fn (PurchaseOrderMail $mail) => $mail->hasTo('supplier@example.com'));
});

test('failed method logs a warning with supplier name and error message', function () {
    Log::shouldReceive('warning')
        ->once()
        ->with('SendPurchaseOrderEmailListener failed', Mockery::on(fn (array $context) => $context['supplier'] === 'Flour Co.'
            && $context['error'] === 'SMTP timeout'));

    $event = new PurchaseOrderRequested(
        supplierEmail: 'supplier@example.com',
        supplierName: 'Flour Co.',
        storeName: 'Sweet Treats Bakery',
        items: [['name' => 'All-Purpose Flour', 'quantity' => 50, 'unit_price' => 2.50]],
        total: 125.00,
        requestedDate: '2026-04-15',
    );

    $listener = new SendPurchaseOrderEmailListener;
    $listener->failed($event, new RuntimeException('SMTP timeout'));
});
