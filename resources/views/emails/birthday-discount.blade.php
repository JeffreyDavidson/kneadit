@extends('emails.layout')

@php
/** @var string $storeName */
/** @var string $primaryColor */
/** @var string $secondaryColor */
/** @var string $storeEmail */
/** @var string $storePhone */
/** @var string $storeAddress */
/** @var string|null $logoUrl */
/** @var \App\Models\Customer $customer */
/** @var \App\Models\Coupon $coupon */
@endphp


@section('title', 'Happy Birthday! 🎂')

@section('badge-color', '#ff69b4')

@section('content')
    <div style="text-align: center; margin-bottom: 30px;">
        <div style="font-size: 48px; margin-bottom: 10px;">🎂🎉</div>
        <h2 style="color: #8b4513; font-size: 32px; margin: 0; font-weight: 700;">
            Happy Birthday, {{ $customer->name }}!
        </h2>
        <p style="font-size: 18px; color: #666; margin-top: 10px;">
            It's your special day, and we have a sweet treat for you!
        </p>
    </div>

    <span class="status-badge" style="background-color: #ff69b4;">Birthday Special</span>

    <div class="order-details" style="background: linear-gradient(135deg, #fff 0%, #ffeef8 100%); border-left-color: #ff69b4;">
        <div style="text-align: center; padding: 20px 0;">
            <h3 style="color: #8b4513; font-size: 24px; margin: 0 0 15px;">Your Birthday Gift!</h3>
            
            <div style="background-color: #ff69b4; color: white; display: inline-block; padding: 15px 25px; border-radius: 25px; font-size: 20px; font-weight: bold; margin: 15px 0;">
                {{ $coupon->value }}% OFF
            </div>
            
            <div style="font-size: 18px; color: #333; margin: 15px 0;">
                <strong>Coupon Code:</strong> 
                <span style="background-color: #f8f5f1; padding: 8px 15px; border-radius: 6px; font-family: monospace; font-size: 20px; font-weight: bold; color: #8b4513;">
                    {{ $coupon->code }}
                </span>
            </div>
        </div>

        <div class="divider"></div>

        <div style="text-align: center;">
            <p style="font-size: 16px; color: #666; margin-bottom: 20px;">
                🍰 Treat yourself to your favorite baked goods! 🧁<br>
                This special birthday discount is valid for <strong>{{ $coupon->expires_at->diffInDays(now()) + 1 }} days</strong>.
            </p>
            
            <div style="background-color: #fff3cd; border: 1px solid #ffeaa7; border-radius: 6px; padding: 15px; margin: 20px 0;">
                <p style="margin: 0; color: #8b4513; font-size: 14px;">
                    <strong>Valid until:</strong> {{ $coupon->expires_at->format('M j, Y') }}<br>
                    @if($coupon->min_order_amount)
                        <strong>Minimum order:</strong> ${{ number_format($coupon->min_order_amount, 2) }}
                    @endif
                </p>
            </div>
        </div>
    </div>

    <div style="text-align: center; margin: 30px 0;">
        <div style="background: linear-gradient(135deg, #d4a574 0%, #c19653 100%); color: white; display: inline-block; padding: 15px 30px; border-radius: 30px; font-size: 18px; font-weight: bold; text-decoration: none; margin: 10px;">
            🛒 Shop Now & Save!
        </div>
    </div>

    <div style="background-color: #f0f8ff; border-radius: 8px; padding: 20px; margin: 25px 0; text-align: center;">
        <h4 style="color: #8b4513; margin: 0 0 15px;">🎉 Birthday Wishes from KneadIt! 🎉</h4>
        <p style="color: #666; margin: 0; font-size: 16px; line-height: 1.6;">
            We hope your special day is filled with sweetness and joy, just like our fresh-baked treats! 
            Thank you for being such a valued customer. Here's to another year of delicious moments together! 🥳
        </p>
    </div>

    <div style="text-align: center; font-size: 14px; color: #999; margin-top: 30px;">
        <p>This birthday discount is exclusively for you and cannot be combined with other offers.</p>
    </div>
@endsection