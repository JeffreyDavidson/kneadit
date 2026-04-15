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


@section('title', "Your Order is Being Prepared - {$storeName}")

@section('badge-color', '#fd7e14')

@section('content')
<p>Hello {{ $customer->name }},</p>

<p><strong>Exciting news!</strong> We're now preparing your delicious order in our bakery kitchen! The wonderful aroma is already filling the air. 🥐</p>

<div class="status-badge" style="background-color: #fd7e14;">
    👨‍🍳 Now Baking
</div>

<div class="order-details">
    <div class="order-number">Order #{{ $order->order_number }}</div>

    @include('emails.partials.order-items', [
        'orderItems' => $orderItems,
        'heading' => 'Items Being Prepared:',
        'showInstructionsLabel' => true,
    ])

    <div class="order-total">
        <div style="display: flex; justify-content: space-between;">
            <span class="total-label">Order Total:</span>
            <span class="total-amount">@money($order->total)</span>
        </div>
    </div>
</div>

@if ($order->delivery_address)
    <div class="delivery-info">
        <div class="info-label">🚚 Delivery Schedule</div>
        <p style="margin: 5px 0;"><strong>Delivery Date:</strong> {{ $order->delivery_date?->format('M j, Y') ?? 'TBD' }}</p>
        <p style="margin: 5px 0;"><strong>Delivery Time:</strong> {{ $order->delivery_time?->format('g:i A') ?? 'TBD' }}</p>
        <p style="margin: 5px 0;"><strong>Address:</strong> {{ $order->delivery_address }}</p>
    </div>
@else
    <div class="delivery-info">
        <div class="info-label">🏪 Pickup Schedule</div>
        <p style="margin: 5px 0;"><strong>Pickup Date:</strong> {{ $order->delivery_date?->format('M j, Y') ?? 'TBD' }}</p>
        <p style="margin: 5px 0;"><strong>Pickup Time:</strong> {{ $order->delivery_time?->format('g:i A') ?? 'TBD' }}</p>
        <p style="margin: 5px 0;"><strong>Location:</strong> {{ $storeAddress ?? '' }}</p>
    </div>
@endif

<div style="background-color: #fff3cd; border-radius: 6px; padding: 15px; margin: 20px 0; border-left: 4px solid #ffc107;">
    <div class="info-label">⏰ What's Next?</div>
    <p style="margin: 5px 0;">Our talented bakers are now working on your order with fresh ingredients and lots of care. We'll notify you as soon as everything is ready!</p>
    <p style="margin: 5px 0;"><strong>Estimated completion:</strong> {{ $order->delivery_date?->format('M j, Y') ?? 'TBD' }} at {{ $order->delivery_time?->format('g:i A') ?? 'TBD' }}</p>
</div>

<p>We take pride in creating each item fresh for you. Thank you for your patience and for choosing {{ $storeName }}!</p>

<p style="color: #666; font-size: 14px;">
    <em>Questions about your order? Just reply to this email or give us a call!</em>
</p>
@endsection