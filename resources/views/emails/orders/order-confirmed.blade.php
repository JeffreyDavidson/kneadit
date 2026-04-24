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


@section('title', "Order #{$order->order_number} Confirmed")

@section('badge-color', '#198754')

@section('content')
<p>Hello {{ $customer->name }},</p>

<p>Great news — your order has been <strong>confirmed</strong> and added to our schedule! 🎉</p>

<div class="status-badge" style="background-color: #198754;">
    ✅ Confirmed
</div>

<div class="order-details">
    <div class="order-number">Order #{{ $order->order_number }}</div>

    @include('emails.partials.order-items', [
        'orderItems' => $orderItems,
        'heading' => 'What you ordered:',
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

<p>We'll send another update once we start preparing your order. Thanks for choosing {{ $storeName }}!</p>

<p style="color: #666; font-size: 14px;">
    <em>Questions about your order? Just reply to this email or give us a call!</em>
</p>
@endsection
