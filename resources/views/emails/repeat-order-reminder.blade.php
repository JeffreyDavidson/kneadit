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
/** @var int $daysSinceLastOrder */
@endphp


@section('title', 'We Miss You!')

@section('badge-color', '#6366f1')

@section('content')
    <div style="text-align: center; margin-bottom: 30px;">
        <div style="font-size: 48px; margin-bottom: 10px;">🥖💭</div>
        <h2 style="color: #8b4513; font-size: 28px; margin: 0; font-weight: 700;">
            We Miss You, {{ $customer->name }}!
        </h2>
        <p style="font-size: 18px; color: #666; margin-top: 10px;">
            It's been a while since your last order...
        </p>
    </div>

    <span class="status-badge" style="background-color: #6366f1;">Come Back & Treat Yourself!</span>

    <div class="order-details" style="background: linear-gradient(135deg, #fff 0%, #eef2ff 100%); border-left-color: #6366f1;">
        <div style="text-align: center; padding: 20px 0;">
            <h3 style="color: #8b4513; font-size: 20px; margin: 0 0 15px;">Your Favorites Are Still Here! 🧁</h3>
            
            <p style="font-size: 16px; color: #666; line-height: 1.6; margin: 20px 0;">
                It's been <strong>{{ $daysSinceLastOrder }} days</strong> since we've had the pleasure of baking for you. 
                We've been keeping your favorite treats warm in our ovens, just waiting for your return!
            </p>
            
            <div style="background-color: #f8f5f1; border-radius: 8px; padding: 20px; margin: 20px 0;">
                <h4 style="color: #8b4513; margin: 0 0 15px; font-size: 18px;">🍰 What's Fresh Today:</h4>
                <div style="color: #666; font-size: 15px; line-height: 1.8;">
                    ✨ Freshly baked artisan breads<br>
                    🧁 Seasonal cupcakes and muffins<br>
                    🥧 Homemade pies and pastries<br>
                    🍪 Warm cookies straight from the oven<br>
                    ☕ Perfect treats for your morning coffee
                </div>
            </div>
        </div>
    </div>

    <div style="text-align: center; margin: 30px 0;">
        <div style="background: linear-gradient(135deg, #d4a574 0%, #c19653 100%); color: white; display: inline-block; padding: 15px 30px; border-radius: 30px; font-size: 18px; font-weight: bold; text-decoration: none; margin: 10px;">
            🛒 Browse Our Menu
        </div>
    </div>

    <div style="background-color: #fff3cd; border: 1px solid #ffeaa7; border-radius: 8px; padding: 20px; margin: 25px 0;">
        <h4 style="color: #8b4513; margin: 0 0 10px; text-align: center;">💡 Did You Know?</h4>
        <p style="color: #666; margin: 0; font-size: 15px; text-align: center; line-height: 1.6;">
            We're always adding new seasonal flavors and limited-time treats to our menu. 
            Check back often to discover something deliciously new!
        </p>
    </div>

    <div style="background-color: #f0f8ff; border-radius: 8px; padding: 20px; margin: 25px 0; text-align: center;">
        <h4 style="color: #8b4513; margin: 0 0 15px;">🏠 Local Delivery Available</h4>
        <p style="color: #666; margin: 0; font-size: 15px; line-height: 1.6;">
            Can't make it to the bakery? No problem! We deliver fresh baked goods right to your door. 
            Order today and enjoy the comfort of home with the taste of our bakery.
        </p>
    </div>

    <div style="text-align: center; margin: 30px 0;">
        <p style="color: #8b4513; font-size: 18px; font-weight: 600; margin-bottom: 15px;">
            We can't wait to serve you again! 🤗
        </p>
        <p style="color: #666; font-size: 16px; line-height: 1.6; margin: 0;">
            Thank you for being such a valued customer. Your support means the world to our small business, 
            and we're always here to make your day a little sweeter.
        </p>
    </div>

    <div style="text-align: center; font-size: 14px; color: #999; margin-top: 30px; padding-top: 20px; border-top: 1px solid #e8e6e3;">
        <p>If you'd prefer not to receive these reminders, please let us know and we'll be happy to adjust your preferences.</p>
    </div>
@endsection