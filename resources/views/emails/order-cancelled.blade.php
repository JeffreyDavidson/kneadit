@extends('emails.layout')

@php
/** @var string $storeName */
/** @var string $primaryColor */
/** @var string $secondaryColor */
/** @var string $storeEmail */
/** @var string $storePhone */
/** @var string $storeAddress */
/** @var string|null $logoUrl */
@endphp


@section('title', 'Order Cancelled - KneadIt Bakery')

@section('badge-color', '#dc3545')

@section('content')
<p>Hello {{ $customer->name }},</p>

<p>We regret to inform you that your order has been cancelled. We sincerely apologize for any inconvenience this may cause.</p>

<div class="status-badge" style="background-color: #dc3545;">
    ❌ Order Cancelled
</div>

<div class="order-details">
    <div class="order-number">Order #{{ $order->order_number }}</div>

    <div class="order-items">
        <h4 style="margin-bottom: 10px; color: #8b4513;">Cancelled Items:</h4>
        @foreach ($orderItems as $item)
            <div class="order-item">
                <div>
                    <div class="item-name">{{ $item->product->name }}</div>
                    <div class="item-details">
                        Quantity: {{ $item->quantity }}
                        @if ($item->special_instructions)
                            <br><em>{{ $item->special_instructions }}</em>
                        @endif
                    </div>
                </div>
                <div class="item-price">${{ number_format($item->total_price, 2) }}</div>
            </div>
        @endforeach
    </div>

    <div class="order-total" style="background-color: #721c24;">
        <div style="display: flex; justify-content: space-between;">
            <span class="total-label">Cancelled Amount:</span>
            <span class="total-amount">${{ number_format($order->total, 2) }}</span>
        </div>
    </div>
</div>

@if ($order->payment_method->value !== 'cash')
    <div style="background-color: #d1ecf1; border-radius: 6px; padding: 15px; margin: 20px 0; border-left: 4px solid #17a2b8;">
        <div class="info-label">💳 Refund Information</div>
        <p style="margin: 5px 0;">Your payment has been cancelled and you will not be charged.</p>
        <p style="margin: 5px 0;"><strong>Original Payment Method:</strong> {{ $order->payment_method->getLabel() }}</p>
        <p style="margin: 5px 0;"><strong>Refund Amount:</strong> ${{ number_format($order->total, 2) }}</p>
        <p style="margin: 10px 0 5px; color: #0c5460;"><em>If you were already charged, the refund will be processed within 3-5 business days.</em></p>
    </div>
@endif

<div style="background-color: #f8d7da; border-radius: 6px; padding: 15px; margin: 20px 0; border-left: 4px solid #dc3545;">
    <div class="info-label">😔 Why was my order cancelled?</div>
    <p style="margin: 5px 0;">Orders may be cancelled for various reasons including:</p>
    <ul style="margin: 5px 0 5px 20px; padding: 0;">
        <li>Ingredient availability issues</li>
        <li>Equipment malfunction</li>
        <li>Unexpected high demand</li>
        <li>Weather or delivery constraints</li>
        <li>Customer request</li>
    </ul>
    <p style="margin: 10px 0 5px; color: #721c24;"><em>We always strive to fulfill every order, and cancellations are rare.</em></p>
</div>

<div style="background-color: #fff3cd; border-radius: 6px; padding: 15px; margin: 20px 0; border-left: 4px solid #ffc107;">
    <div class="info-label">🤝 We'd Love to Make It Right</div>
    <p style="margin: 5px 0;">We understand this is disappointing and want to make it up to you:</p>
    <ul style="margin: 5px 0 5px 20px; padding: 0;">
        <li><strong>10% discount</strong> on your next order</li>
        <li><strong>Priority booking</strong> for future orders</li>
        <li><strong>Free consultation</strong> for custom orders</li>
    </ul>
    <p style="margin: 10px 0 5px; color: #856404;"><em>Contact us to claim your discount code: (555) 123-BAKE</em></p>
</div>

@if ($order->delivery_date?->isFuture())
    <div style="background-color: #d4edda; border-radius: 6px; padding: 15px; margin: 20px 0; border-left: 4px solid #28a745;">
        <div class="info-label">🗓️ Reschedule Your Order</div>
        <p style="margin: 5px 0;">Since your original date was {{ $order->delivery_date?->format('M j, Y') ?? 'TBD' }}, you may still have time to place a new order!</p>
        <p style="margin: 5px 0;"><strong>Call us at (555) 123-BAKE</strong> and we'll help you find an alternative solution.</p>
    </div>
@endif

<p>We sincerely apologize for the inconvenience and truly appreciate your understanding. Your satisfaction is our top priority, and we hope to serve you again soon.</p>

@if ($order->notes)
    <div style="background-color: #f8f9fa; border-radius: 6px; padding: 15px; margin: 15px 0; border-left: 4px solid #6c757d;">
        <div class="info-label">📝 Original Order Notes</div>
        <p style="margin: 5px 0; color: #495057;">{{ $order->notes }}</p>
    </div>
@endif

<p><strong>Questions or concerns?</strong> Please don't hesitate to contact us. We're here to help and want to ensure you have a positive experience with KneadIt Bakery.</p>

<p style="color: #666; font-size: 14px;">
    <em>Contact us: (555) 123-BAKE | hello@kneaditbakery.com</em>
</p>
@endsection