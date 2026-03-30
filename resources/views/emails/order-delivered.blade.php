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


@section('title', 'Order Delivered! - KneadIt Bakery')

@section('badge-color', '#28a745')

@section('content')
<p>Hello {{ $customer->name }},</p>

<p><strong>🚚 Delivery Complete!</strong> Your order has been successfully delivered. We hope you love every delicious bite!</p>

<div class="status-badge" style="background-color: #28a745;">
    ✅ Delivered
</div>

<div class="order-details">
    <div class="order-number">Order #{{ $order->order_number }}</div>
    
    <div class="order-items">
        <h4 style="margin-bottom: 10px; color: #8b4513;">Delivered Items:</h4>
        @foreach($orderItems as $item)
            <div class="order-item">
                <div>
                    <div class="item-name">✅ {{ $item->product->name }}</div>
                    <div class="item-details">
                        Quantity: {{ $item->quantity }}
                        @if($item->special_instructions)
                            <br><em>{{ $item->special_instructions }}</em>
                        @endif
                    </div>
                </div>
                <div class="item-price">${{ number_format($item->total_price, 2) }}</div>
            </div>
        @endforeach
    </div>

    <div class="order-total">
        <div style="display: flex; justify-content: space-between;">
            <span class="total-label">Order Total:</span>
            <span class="total-amount">${{ number_format($order->total, 2) }}</span>
        </div>
    </div>
</div>

<div class="delivery-info" style="background-color: #d4edda; border-left: 4px solid #28a745;">
    <div class="info-label">📍 Delivery Confirmed</div>
    <p style="margin: 5px 0;"><strong>Delivered to:</strong> {{ $order->delivery_address }}</p>
    <p style="margin: 5px 0;"><strong>Delivery Time:</strong> {{ now()->format('M j, Y \a\t g:i A') }}</p>
    <p style="margin: 10px 0 5px; color: #155724;"><em>Your fresh baked goods have been safely delivered and are ready to enjoy!</em></p>
</div>

<div style="background-color: #fff3cd; border-radius: 6px; padding: 15px; margin: 20px 0; border-left: 4px solid #ffc107;">
    <div class="info-label">🍞 Storage Tips</div>
    <p style="margin: 5px 0;">To keep your baked goods fresh:</p>
    <ul style="margin: 5px 0 5px 20px; padding: 0;">
        <li>Store bread and pastries in a cool, dry place</li>
        <li>Keep items in their packaging until ready to serve</li>
        <li>Refrigerate items with cream or custard fillings</li>
        <li>Best enjoyed within 2-3 days for optimal freshness</li>
    </ul>
</div>

<div style="background-color: #d1ecf1; border-radius: 6px; padding: 15px; margin: 20px 0; border-left: 4px solid #17a2b8;">
    <div class="info-label">⭐ We'd Love Your Feedback!</div>
    <p style="margin: 5px 0;">How was your experience? Your feedback helps us continue to improve and serve you better!</p>
    <p style="margin: 5px 0;">
        <strong>Share your thoughts:</strong><br>
        📧 Email us at hello@kneaditbakery.com<br>
        📱 Tag us on social media @kneaditbakery<br>
        ⭐ Leave a review on our website
    </p>
</div>

<p>Thank you for choosing KneadIt Bakery! It was our pleasure to create and deliver these fresh treats for you.</p>

<p><strong>Come back soon!</strong> We're always baking up something new and delicious. Check out our website for daily specials and seasonal items.</p>

@if($order->notes)
    <div style="background-color: #f8f9fa; border-radius: 6px; padding: 15px; margin: 15px 0; border-left: 4px solid #6c757d;">
        <div class="info-label">📝 Order Notes</div>
        <p style="margin: 5px 0; color: #495057;">{{ $order->notes }}</p>
    </div>
@endif

<p style="color: #666; font-size: 14px;">
    <em>Questions or concerns about your delivery? Contact us at (555) 123-BAKE or reply to this email.</em>
</p>
@endsection