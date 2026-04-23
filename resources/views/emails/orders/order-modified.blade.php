@use(App\Presenters\OrderItemPresenter)
@extends('emails.layout')

@php
/** @var App\Models\Orders\Order $order */
/** @var App\ValueObjects\Money $previousSubtotal */
/** @var App\ValueObjects\Money $previousTotal */
/** @var string $primaryColor */
/** @var string $secondaryColor */
@endphp

@section('title', 'Order Updated')

@section('content')
    <p style="margin: 0 0 15px;">Hello {{ $order->customer?->name }},</p>

    <p style="margin: 0 0 20px;">Your order <strong>#{{ $order->order_number }}</strong> has been updated. Here's the latest summary.</p>

    <div style="background-color: #fef9ef; border-radius: 8px; padding: 20px; margin: 0 0 20px;">
        <h3 style="margin: 0 0 12px; color: {{ $secondaryColor }};">Items</h3>
        @foreach ($order->orderItems as $item)
            <div style="display: flex; justify-content: space-between; margin-bottom: 6px; font-size: 14px;">
                <span>{{ $item->quantity }} × {{ OrderItemPresenter::for($item)->productName() }}</span>
                <span style="font-weight: 600; color: {{ $secondaryColor }};">@money(OrderItemPresenter::for($item)->totalPrice())</span>
            </div>
        @endforeach

        <div style="margin-top: 12px; padding-top: 10px; border-top: 2px solid {{ $primaryColor }}; text-align: right;">
            <div style="margin-bottom: 4px; font-size: 14px; color: #555;">Previous Total: @money($previousTotal)</div>
            <div style="font-size: 20px; font-weight: 700; color: {{ $secondaryColor }};">New Total: @money($order->total)</div>
        </div>
    </div>

    <p style="margin: 0 0 20px; font-size: 14px; color: #555;">If this update wasn't intentional, please reply to this email or contact us right away.</p>
@endsection
