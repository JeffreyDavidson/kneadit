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
@php
/** @var \App\Models\Customer $customer */
/** @var \App\Models\Coupon|null $coupon */
@endphp


@section('title', 'Happy Birthday! 🎂')

@section('badge-color', '#ff69b4')

@section('content')
    <div style="text-align: center; margin-bottom: 30px;">
        <div style="font-size: 64px; margin-bottom: 10px;">🎂🎉🎈</div>
        <h2 style="color: #8b4513; font-size: 32px; margin: 0; font-weight: 700;">
            Happy Birthday, {{ $customer->name }}!
        </h2>
        <p style="font-size: 18px; color: #666; margin-top: 10px;">
            From all of us at <strong>{{ $storeName }}</strong>, we wish you the sweetest birthday!
        </p>
    </div>

    <span class="status-badge" style="background-color: #ff69b4;">🎂 Birthday Special</span>

    <div style="background: linear-gradient(135deg, #ffeef8 0%, #fff5f9 100%); border-radius: 12px; padding: 30px; margin: 20px 0; text-align: center;">
        <p style="font-size: 18px; color: #333; line-height: 1.6; margin: 0 0 20px;">
            🎉 It's your special day and we wanted to make it even sweeter! 
            You're a treasured part of our bakery family, and we're so grateful to have you.
        </p>

        @if($coupon)
            <div style="background-color: white; border: 2px dashed #ff69b4; border-radius: 12px; padding: 25px; margin: 20px 0;">
                <h3 style="color: #8b4513; font-size: 22px; margin: 0 0 15px;">🎁 Your Birthday Gift!</h3>
                
                <div style="background-color: #ff69b4; color: white; display: inline-block; padding: 15px 30px; border-radius: 25px; font-size: 24px; font-weight: bold; margin: 10px 0;">
                    {{ number_format($coupon->value) }}% OFF
                </div>
                
                <div style="font-size: 18px; color: #333; margin: 15px 0;">
                    <strong>Use Code:</strong> 
                    <span style="background-color: #f8f5f1; padding: 8px 15px; border-radius: 6px; font-family: monospace; font-size: 20px; font-weight: bold; color: #8b4513; letter-spacing: 2px;">
                        {{ $coupon->code }}
                    </span>
                </div>
                
                <p style="font-size: 14px; color: #888; margin: 10px 0 0;">
                    Valid until {{ $coupon->expires_at->format('M j, Y') }} • Single use
                </p>
            </div>
        @endif
    </div>

    <div style="text-align: center; margin: 25px 0;">
        <p style="font-size: 16px; color: #666; line-height: 1.6;">
            🍰 Whether it's your favorite bread, a decadent cake, or fresh pastries — 
            treat yourself today! You deserve it! 🧁
        </p>
    </div>

    <div style="background-color: #f0f8ff; border-radius: 8px; padding: 20px; margin: 25px 0; text-align: center;">
        <p style="color: #8b4513; margin: 0; font-size: 16px;">
            🥳 Here's to another wonderful year filled with delicious moments!
        </p>
        <p style="color: #888; margin: 10px 0 0; font-size: 14px;">
            With love and fresh-baked wishes,<br>
            <strong>{{ $storeName }}</strong>
        </p>
    </div>
@endsection
