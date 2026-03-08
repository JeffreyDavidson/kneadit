@extends('emails.layout')

@section('title', 'Order Confirmed')

@section('content')
<p style="margin: 0 0 15px;">Hello {{ $customer->name }},</p>

<p style="margin: 0 0 20px;">Great news — your order has been confirmed! Here's a summary of what you ordered.</p>

<div style="background-color: #fef9ef; border-radius: 8px; padding: 20px; margin: 0 0 20px; border-left: 4px solid #d4920c;">
    <div style="font-size: 18px; font-weight: 700; color: #1c1410; margin-bottom: 15px;">Order #{{ $order->order_number }}</div>

    @foreach($orderItems as $item)
        <div style="padding: 8px 0; border-bottom: 1px solid #e8e3d8; display: flex; justify-content: space-between;">
            <span style="color: #1c1410;">{{ $item->product->name }} × {{ $item->quantity }}</span>
            <span style="font-weight: 600; color: #1c1410;">${{ number_format($item->unit_price * $item->quantity, 2) }}</span>
        </div>
    @endforeach

    <div style="margin-top: 12px; padding-top: 10px; border-top: 2px solid #d4920c; text-align: right;">
        @if($order->delivery_fee > 0)
            <div style="margin-bottom: 4px; font-size: 14px; color: #555;">Subtotal: ${{ number_format($order->subtotal, 2) }}</div>
            <div style="margin-bottom: 4px; font-size: 14px; color: #555;">Delivery Fee: ${{ number_format($order->delivery_fee, 2) }}</div>
        @endif
        <div style="font-size: 20px; font-weight: 700; color: #1c1410;">Total: ${{ number_format($order->total, 2) }}</div>
    </div>
</div>

@if($order->delivery_address)
    <div style="background-color: #fef9ef; border-radius: 8px; padding: 15px; margin: 0 0 20px; border-left: 4px solid #d4920c;">
        <p style="margin: 0 0 5px; font-weight: 600;">🚚 Delivery Details</p>
        <p style="margin: 0 0 3px;"><strong>Address:</strong> {{ $order->delivery_address }}</p>
        <p style="margin: 0 0 3px;"><strong>Date:</strong> {{ $order->requested_date->format('M j, Y') }}</p>
        <p style="margin: 0;"><strong>Time:</strong> {{ $order->requested_time->format('g:i A') }}</p>
    </div>
@else
    <div style="background-color: #fef9ef; border-radius: 8px; padding: 15px; margin: 0 0 20px; border-left: 4px solid #d4920c;">
        <p style="margin: 0 0 5px; font-weight: 600;">🏪 Pickup Details</p>
        <p style="margin: 0 0 3px;"><strong>Date:</strong> {{ $order->requested_date->format('M j, Y') }}</p>
        <p style="margin: 0 0 3px;"><strong>Time:</strong> {{ $order->requested_time->format('g:i A') }}</p>
        <p style="margin: 0;"><strong>Location:</strong> {{ \App\Models\Setting::get('store_address', '') }}</p>
    </div>
@endif

@if($order->notes)
    <div style="background-color: #fff9e6; border-radius: 6px; padding: 12px; margin: 0 0 20px; border-left: 4px solid #d4920c;">
        <p style="margin: 0; font-size: 14px;">📝 <strong>Notes:</strong> {{ $order->notes }}</p>
    </div>
@endif

<div style="text-align: center; margin: 25px 0;">
    <a href="{{ url('/track') }}" style="display: inline-block; background-color: #d4920c; color: #ffffff; text-decoration: none; padding: 14px 32px; border-radius: 8px; font-weight: 600; font-size: 16px;">Track Your Order</a>
</div>

<p style="margin: 0; color: #555; font-size: 14px;">We'll keep you updated as your order progresses. Thank you for your order!</p>
@endsection
