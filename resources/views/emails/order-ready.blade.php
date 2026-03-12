@extends('emails.layout')

@section('title', 'Your Order is Ready!')

@section('content')
<p style="margin: 0 0 15px;">Hello {{ $customer->name }},</p>

@if($order->delivery_address)
    <p style="margin: 0 0 20px; font-size: 18px; font-weight: 600; color: {{ $primaryColor }};">🚚 Your order is on its way!</p>

    <div style="background-color: #fef9ef; border-radius: 8px; padding: 20px; margin: 0 0 20px; border-left: 4px solid {{ $primaryColor }};">
        <div style="font-size: 18px; font-weight: 700; color: {{ $secondaryColor }}; margin-bottom: 10px;">Order #{{ $order->order_number }}</div>
        <p style="margin: 0 0 5px;"><strong>Delivery Address:</strong> {{ $order->delivery_address }}</p>
        <p style="margin: 0 0 5px;"><strong>Estimated Time:</strong> {{ $order->requested_time->format('g:i A') }}</p>
        <p style="margin: 10px 0 0; color: #555; font-size: 14px;"><em>Please ensure someone is available to receive the delivery.</em></p>
    </div>
@else
    <p style="margin: 0 0 20px; font-size: 18px; font-weight: 600; color: {{ $primaryColor }};">🎉 Your order is ready for pickup!</p>

    <div style="background-color: #fef9ef; border-radius: 8px; padding: 20px; margin: 0 0 20px; border-left: 4px solid {{ $primaryColor }};">
        <div style="font-size: 18px; font-weight: 700; color: {{ $secondaryColor }}; margin-bottom: 10px;">Order #{{ $order->order_number }}</div>
        <p style="margin: 0 0 5px;"><strong>Pickup Location:</strong> {{ \App\Models\Setting::get('store_address', '') }}</p>
        @if(\App\Models\Setting::get('store_phone'))
            <p style="margin: 0 0 5px;"><strong>Phone:</strong> {{ \App\Models\Setting::get('store_phone') }}</p>
        @endif
        <p style="margin: 10px 0 0; color: #555; font-size: 14px;"><em>Just mention your name or order number when you arrive!</em></p>
    </div>
@endif

<div style="text-align: center; margin: 25px 0;">
    <a href="{{ url('/track') }}" style="display: inline-block; background-color: {{ $primaryColor }}; color: #ffffff; text-decoration: none; padding: 14px 32px; border-radius: 8px; font-weight: 600; font-size: 16px;">Track Your Order</a>
</div>

<p style="margin: 0; color: #555; font-size: 14px;">Thank you for choosing {{ \App\Models\Setting::get('store_name', 'KneadIt Bakery') }}!</p>
@endsection
