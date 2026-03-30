@extends('emails.layout')

@php
/** @var string $storeName */
/** @var string $primaryColor */
/** @var string $secondaryColor */
/** @var string $storeEmail */
/** @var string $storePhone */
/** @var string $storeAddress */
/** @var string|null $logoUrl */
/** @var \App\Models\Order $order */
/** @var string $reviewUrl */
/** @var \Illuminate\Database\Eloquent\Collection<int, \App\Models\OrderItem> $orderItems */
@endphp


@section('title', 'How was your order?')

@section('content')
    <div style="text-align: center; margin-bottom: 20px;">
        <h2 style="margin: 0 0 8px; font-size: 22px; color: {{ $secondaryColor }};">How was your order? 🍰</h2>
        <p style="margin: 0; color: #6b5c4d; font-size: 15px;">We'd love to hear from you, {{ $order->customer->name }}!</p>
    </div>

    <div style="background-color: #fef9ef; border-radius: 10px; padding: 15px 20px; margin-bottom: 20px;">
        <p style="margin: 0 0 8px; font-size: 13px; color: #6b5c4d; text-transform: uppercase;">Order #{{ $order->order_number }}</p>
        @foreach($orderItems as $item)
            <p style="margin: 0 0 4px; font-size: 14px; color: {{ $secondaryColor }};">
                {{ $item->quantity }}× {{ $item->product->name ?? 'Item' }}
            </p>
        @endforeach
    </div>

    <p style="text-align: center; margin: 0 0 20px; font-size: 15px; color: {{ $secondaryColor }};">
        Tap a star to leave your review:
    </p>

    <div style="text-align: center; margin-bottom: 25px;">
        @for($i = 1; $i <= 5; $i++)
            <a href="{{ $reviewUrl }}?rating={{ $i }}" style="text-decoration: none; font-size: 36px; margin: 0 3px;">⭐</a>
        @endfor
    </div>

    <div style="text-align: center;">
        <a href="{{ $reviewUrl }}" style="display: inline-block; background-color: {{ $primaryColor }}; color: #ffffff; text-decoration: none; padding: 12px 30px; border-radius: 8px; font-weight: 600; font-size: 15px;">Write a Review</a>
    </div>

    <p style="text-align: center; margin: 20px 0 0; font-size: 13px; color: #6b5c4d;">
        Thank you for supporting {{ $storeName }}! Your feedback helps us bake even better. 💛
    </p>
@endsection
