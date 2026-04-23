@extends('emails.layout')

@php
/** @var App\Models\Orders\Cart $cart */
/** @var App\Models\Financial\Coupon|null $coupon */
/** @var string $recoveryUrl */
/** @var string $primaryColor */
/** @var string $secondaryColor */
@endphp

@section('title', 'You left something in your cart')

@section('content')
    <p style="margin: 0 0 15px;">Hi {{ $cart->customer_name ?: 'there' }},</p>

    <p style="margin: 0 0 20px;">Looks like you started building an order but didn't get to check out. Your items are still saved — just one click to pick up where you left off.</p>

    <div style="background-color: #fef9ef; border-radius: 8px; padding: 20px; margin: 0 0 20px;">
        <p style="margin: 0 0 10px; font-size: 13px; color: #555; text-transform: uppercase; letter-spacing: 1px;">In your cart</p>
        @foreach ($cart->items as $item)
            <div style="padding: 8px 0; font-size: 14px; color: {{ $secondaryColor }};">
                {{ $item->quantity }} × {{ $item->product?->name ?? 'Product' }}
            </div>
        @endforeach
    </div>

    @if ($coupon)
        <div style="background-color: #fff; border: 2px dashed {{ $primaryColor }}; border-radius: 8px; padding: 20px; margin: 0 0 20px; text-align: center;">
            <p style="margin: 0 0 6px; font-size: 13px; color: #555; text-transform: uppercase; letter-spacing: 1px;">Your welcome-back coupon</p>
            <p style="margin: 0 0 8px; font-size: 26px; font-weight: 700; letter-spacing: 2px; color: {{ $secondaryColor }};">{{ $coupon->code }}</p>
            <p style="margin: 0; font-size: 14px; color: #555;">@money($coupon->fixed_amount) off — applied automatically when you finish checking out.</p>
        </div>
    @endif

    <div style="text-align: center; margin: 24px 0;">
        <a href="{{ $recoveryUrl }}" style="display: inline-block; padding: 14px 28px; background-color: {{ $primaryColor }}; color: #fff; text-decoration: none; border-radius: 8px; font-weight: 600;">
            Finish my order
        </a>
    </div>

    <p style="margin: 0; font-size: 13px; color: #888; text-align: center;">Not interested anymore? No worries — your cart will quietly expire in a few weeks.</p>
@endsection
