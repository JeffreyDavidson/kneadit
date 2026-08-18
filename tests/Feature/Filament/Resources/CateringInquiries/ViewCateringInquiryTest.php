<?php

use App\Enums\Customers\CateringInquiryStatus;
use App\Enums\Orders\OrderStatus;
use App\Events\Marketing\CateringQuoteRequested;
use App\Filament\Resources\CateringInquiries\Pages\ViewCateringInquiry;
use App\Models\Customers\CateringInquiry;
use App\Models\Orders\Order;
use App\Models\Staff\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    test()->actingAs(User::factory()->owner()->create());
});

test('renders the view page with the inquiry summary', function () {
    $inquiry = CateringInquiry::factory()->create([
        'customer_name' => 'Maya Patel',
        'customer_email' => 'maya@example.com',
        'event_type' => 'Wedding',
        'guest_count' => 120,
        'quoted_amount' => 4200,
        'status' => CateringInquiryStatus::Quoted,
    ]);

    Livewire::test(ViewCateringInquiry::class, ['record' => $inquiry->getRouteKey()])
        ->assertOk()
        ->assertSee('Maya Patel')
        ->assertSee('Wedding')
        ->assertSee('120 guests')
        ->assertSee('Quote');
});

test('send quote is visible for an Inquiry with a quoted amount and dispatches the event', function () {
    Event::fake();

    $inquiry = CateringInquiry::factory()->create([
        'status' => CateringInquiryStatus::Inquiry,
        'quoted_amount' => 1000,
    ]);

    Livewire::test(ViewCateringInquiry::class, ['record' => $inquiry->getRouteKey()])
        ->assertActionVisible('sendQuote')
        ->assertActionHidden('resendQuote')
        ->callAction('sendQuote');

    expect($inquiry->fresh()->status)->toBe(CateringInquiryStatus::Quoted);
    Event::assertDispatched(CateringQuoteRequested::class);
});

test('send quote is hidden when no amount has been set', function () {
    $inquiry = CateringInquiry::factory()->create([
        'status' => CateringInquiryStatus::Inquiry,
        'quoted_amount' => null,
    ]);

    Livewire::test(ViewCateringInquiry::class, ['record' => $inquiry->getRouteKey()])
        ->assertActionHidden('sendQuote')
        ->assertActionVisible('manageQuoteItems');
});

test('resend quote is visible when status is Quoted and dispatches the event', function () {
    Event::fake();

    $inquiry = CateringInquiry::factory()->create([
        'status' => CateringInquiryStatus::Quoted,
        'quoted_amount' => 800,
    ]);

    Livewire::test(ViewCateringInquiry::class, ['record' => $inquiry->getRouteKey()])
        ->assertActionVisible('resendQuote')
        ->assertActionHidden('sendQuote')
        ->callAction('resendQuote');

    expect($inquiry->fresh()->status)->toBe(CateringInquiryStatus::Quoted);
    Event::assertDispatched(CateringQuoteRequested::class);
});

test('manage quote items adds items, recomputes total, and does not email', function () {
    Event::fake();

    $inquiry = CateringInquiry::factory()->create([
        'status' => CateringInquiryStatus::Inquiry,
        'quoted_amount' => null,
    ]);

    Livewire::test(ViewCateringInquiry::class, ['record' => $inquiry->getRouteKey()])
        ->callAction('manageQuoteItems', data: [
            'items' => [
                ['name' => 'Cake', 'quantity' => 1, 'unit_price' => 400, 'special_instructions' => null],
                ['name' => 'Macarons', 'quantity' => 100, 'unit_price' => 3, 'special_instructions' => null],
            ],
        ]);

    $inquiry->refresh();
    expect($inquiry->items)->toHaveCount(2)
        ->and($inquiry->status)->toBe(CateringInquiryStatus::Inquiry)
        ->and($inquiry->quoted_amount?->dollars())->toBe(700.00);
    Event::assertNotDispatched(CateringQuoteRequested::class);
});

test('confirm booking creates an order, transitions inquiry to Confirmed', function () {
    $quoted = CateringInquiry::factory()->create(['status' => CateringInquiryStatus::Quoted, 'quoted_amount' => 500]);

    Livewire::test(ViewCateringInquiry::class, ['record' => $quoted->getRouteKey()])
        ->assertActionVisible('confirmBooking')
        ->callAction('confirmBooking');

    $quoted->refresh();
    expect($quoted->status)->toBe(CateringInquiryStatus::Confirmed)
        ->and($quoted->order)->not->toBeNull()
        ->and($quoted->order->status)->toBe(OrderStatus::Confirmed);
});

test('confirm booking is hidden once an order exists', function () {
    $inquiry = CateringInquiry::factory()->create(['status' => CateringInquiryStatus::Confirmed, 'quoted_amount' => 500]);
    Order::factory()->for($inquiry, 'cateringInquiry')->create();

    Livewire::test(ViewCateringInquiry::class, ['record' => $inquiry->getRouteKey()])
        ->assertActionHidden('confirmBooking');
});

test('confirm booking is hidden when not Quoted', function () {
    $inquiry = CateringInquiry::factory()->create(['status' => CateringInquiryStatus::Inquiry]);

    Livewire::test(ViewCateringInquiry::class, ['record' => $inquiry->getRouteKey()])
        ->assertActionHidden('confirmBooking');
});

test('mark deposit received persists the deposit fields and promotes status', function () {
    $inquiry = CateringInquiry::factory()->create([
        'status' => CateringInquiryStatus::Quoted,
        'quoted_amount' => 1000,
        'deposit_paid_at' => null,
    ]);

    Livewire::test(ViewCateringInquiry::class, ['record' => $inquiry->getRouteKey()])
        ->assertActionVisible('markDepositReceived')
        ->callAction('markDepositReceived', data: ['amount' => 250, 'reference' => 'CHK-9000']);

    $inquiry->refresh();
    expect($inquiry->deposit_amount?->dollars())->toBe(250.00)
        ->and($inquiry->deposit_paid_at)->not->toBeNull()
        ->and($inquiry->deposit_reference)->toBe('CHK-9000')
        ->and($inquiry->status)->toBe(CateringInquiryStatus::Confirmed);
});

test('mark deposit received is hidden once a deposit is recorded', function () {
    $inquiry = CateringInquiry::factory()->create([
        'status' => CateringInquiryStatus::Confirmed,
        'quoted_amount' => 1000,
        'deposit_paid_at' => now(),
        'deposit_amount' => 250,
    ]);

    Livewire::test(ViewCateringInquiry::class, ['record' => $inquiry->getRouteKey()])
        ->assertActionHidden('markDepositReceived');
});

test('cancel action transitions to Cancelled and prepends a reason to notes', function () {
    $inquiry = CateringInquiry::factory()->create([
        'status' => CateringInquiryStatus::Quoted,
        'notes' => 'Existing.',
    ]);

    Livewire::test(ViewCateringInquiry::class, ['record' => $inquiry->getRouteKey()])
        ->assertActionVisible('cancel')
        ->callAction('cancel', data: ['reason' => 'Customer rescheduled']);

    $inquiry->refresh();
    expect($inquiry->status)->toBe(CateringInquiryStatus::Cancelled)
        ->and($inquiry->notes)->toContain('Customer rescheduled')
        ->and($inquiry->notes)->toContain('Existing.');
});

test('cancel is hidden when already Completed or Cancelled', function (CateringInquiryStatus $status) {
    $inquiry = CateringInquiry::factory()->create(['status' => $status]);

    Livewire::test(ViewCateringInquiry::class, ['record' => $inquiry->getRouteKey()])
        ->assertActionHidden('cancel');
})->with([
    'completed' => [CateringInquiryStatus::Completed],
    'cancelled' => [CateringInquiryStatus::Cancelled],
]);

test('edit notes action persists changes', function () {
    $inquiry = CateringInquiry::factory()->create(['notes' => null]);

    Livewire::test(ViewCateringInquiry::class, ['record' => $inquiry->getRouteKey()])
        ->callAction('editNotes', data: ['notes' => 'Talked to chef about gluten-free options.']);

    expect($inquiry->fresh()->notes)->toBe('Talked to chef about gluten-free options.');
});

test('a cancelled inquiry hides the quote, booking, and deposit action buttons', function () {
    $inquiry = CateringInquiry::factory()->create([
        'status' => CateringInquiryStatus::Cancelled,
        'quoted_amount' => 11800,
        'deposit_paid_at' => null,
    ]);

    Livewire::test(ViewCateringInquiry::class, ['record' => $inquiry->getRouteKey()])
        ->assertOk()
        ->assertDontSee('Manage items')
        ->assertDontSee('Send quote')
        ->assertDontSee('Resend')
        ->assertDontSee('Confirm booking')
        ->assertDontSee('Mark deposit received');
});

test('edit customer action updates contact fields', function () {
    $inquiry = CateringInquiry::factory()->create(['customer_name' => 'Old Name']);

    Livewire::test(ViewCateringInquiry::class, ['record' => $inquiry->getRouteKey()])
        ->callAction('editCustomer', data: [
            'customer_name' => 'New Name',
            'customer_email' => 'new@example.com',
            'customer_phone' => '555-0123',
        ]);

    expect($inquiry->fresh()->customer_name)->toBe('New Name');
});
