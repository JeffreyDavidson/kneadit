<?php

use App\Events\PurchaseOrderRequested;
use App\Listeners\SendPurchaseOrderEmailListener;
use App\Mail\PurchaseOrderMail;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
