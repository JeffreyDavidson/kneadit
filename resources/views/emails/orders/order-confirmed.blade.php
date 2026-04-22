@use(App\Presenters\OrderItemPresenter)
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


@section('title', 'Order Confirmed')

@section('content')
<p style="margin: 0 0 15px;">Hello {{ $customer->name }},</p>

<p style="margin: 0 0 20px;">Great news — your order has been confirmed! Here's a summary of what you ordered.</p>

<div style="background-color: #fef9ef; border-radius: 8px; padding: 20px; margin: 0 0 20px; border-left: 4px solid {{ $primaryColor }};">
    <div style="font-size: 18px; font-weight: 700; color: {{ $secondaryColor }}; margin-bottom: 15px;">Order #{{ $order->order_number }}</div>

    @foreach ($orderItems as $item)
        <div style="padding: 8px 0; border-bottom: 1px solid #e8e3d8; display: flex; justify-content: space-between;">
            <span style="color: {{ $secondaryColor }};">{{ $item->product->name }} × {{ $item->quantity }}</span>
            <span style="font-weight: 600; color: {{ $secondaryColor }};">@money(OrderItemPresenter::for($item)->totalPrice())</span>
        </div>
    @endforeach

    <div style="margin-top: 12px; padding-top: 10px; border-top: 2px solid {{ $primaryColor }}; text-align: right;">
        @if ($order->delivery_fee->isPositive())
            <div style="margin-bottom: 4px; font-size: 14px; color: #555;">Subtotal: @money($order->subtotal)</div>
            <div style="margin-bottom: 4px; font-size: 14px; color: #555;">Delivery Fee: @money($order->delivery_fee)</div>
        @endif
        <div style="font-size: 20px; font-weight: 700; color: {{ $secondaryColor }};">Total: @money($order->total)</div>
    </div>
</div>

@if ($order->delivery_address)
    <div style="background-color: #fef9ef; border-radius: 8px; padding: 15px; margin: 0 0 20px; border-left: 4px solid {{ $primaryColor }};">
        <p style="margin: 0 0 5px; font-weight: 600;">🚚 Delivery Details</p>
        <p style="margin: 0 0 3px;"><strong>Address:</strong> {{ $order->delivery_address }}</p>
        <p style="margin: 0 0 3px;"><strong>Date:</strong> {{ $order->delivery_date?->format('M j, Y') ?? 'TBD' }}</p>
        <p style="margin: 0;"><strong>Time:</strong> {{ $order->delivery_time?->format('g:i A') ?? 'TBD' }}</p>
    </div>
@else
    <div style="background-color: #fef9ef; border-radius: 8px; padding: 15px; margin: 0 0 20px; border-left: 4px solid {{ $primaryColor }};">
        <p style="margin: 0 0 5px; font-weight: 600;">🏪 Pickup Details</p>
        <p style="margin: 0 0 3px;"><strong>Date:</strong> {{ $order->delivery_date?->format('M j, Y') ?? 'TBD' }}</p>
        <p style="margin: 0 0 3px;"><strong>Time:</strong> {{ $order->delivery_time?->format('g:i A') ?? 'TBD' }}</p>
        <p style="margin: 0;"><strong>Location:</strong> {{ $storeAddress }}</p>
    </div>
@endif

@if ($order->notes)
    <div style="background-color: #fff9e6; border-radius: 6px; padding: 12px; margin: 0 0 20px; border-left: 4px solid {{ $primaryColor }};">
        <p style="margin: 0; font-size: 14px;">📝 <strong>Notes:</strong> {{ $order->notes }}</p>
    </div>
@endif

<div style="text-align: center; margin: 25px 0;">
    <a href="{{ url('/track') }}" style="display: inline-block; background-color: {{ $primaryColor }}; color: #ffffff; text-decoration: none; padding: 14px 32px; border-radius: 8px; font-weight: 600; font-size: 16px;">Track Your Order</a>
</div>


@if (!empty($emailPolicies))
<div style="background-color: #f9f6f1; border-radius: 6px; padding: 15px; margin: 20px 0 20px; border-top: 2px solid {{ $primaryColor }};">
    <p style="margin: 0 0 10px; font-weight: 700; font-size: 14px; color: {{ $secondaryColor }};">Order Terms</p>
    @foreach ($emailPolicies as $label => $text)
    <p style="margin: 0 0 6px; font-size: 12px; color: #555;"><strong>{{ $label }}:</strong> {{ $text }}</p>
    @endforeach
</div>
@endif

<p style="margin: 0; color: #555; font-size: 14px;">We'll keep you updated as your order progresses. Thank you for your order!</p>
@endsection
