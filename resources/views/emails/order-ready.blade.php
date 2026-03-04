@extends('emails.layout')

@section('title', 'Your Order is Ready! - KneadIt Bakery')

@section('badge-color', '#17a2b8')

@section('content')
<p>Hello {{ $customer->name }},</p>

<p><strong>🎉 Your order is ready!</strong> All your delicious treats have been freshly prepared and are waiting for you. They smell absolutely amazing!</p>

<div class="status-badge" style="background-color: #17a2b8;">
    ✨ Ready for {{ $order->delivery_address ? 'Delivery' : 'Pickup' }}
</div>

<div class="order-details">
    <div class="order-number">Order #{{ $order->order_number }}</div>
    
    <div class="order-items">
        <h4 style="margin-bottom: 10px; color: #8b4513;">Your Fresh Items:</h4>
        @foreach($orderItems as $item)
            <div class="order-item">
                <div>
                    <div class="item-name">✅ {{ $item->product->name }}</div>
                    <div class="item-details">
                        Quantity: {{ $item->quantity }} - <em>Freshly prepared!</em>
                        @if($item->special_instructions)
                            <br><em>Prepared with: {{ $item->special_instructions }}</em>
                        @endif
                    </div>
                </div>
                <div class="item-price">${{ number_format($item->total_price, 2) }}</div>
            </div>
        @endforeach
    </div>

    <div class="order-total">
        <div style="display: flex; justify-content: space-between;">
            <span class="total-label">Total Paid:</span>
            <span class="total-amount">${{ number_format($order->total, 2) }}</span>
        </div>
    </div>
</div>

@if($order->delivery_address)
    <div class="delivery-info" style="background-color: #d1ecf1; border-left: 4px solid #17a2b8;">
        <div class="info-label">🚚 Delivery Information</div>
        <p style="margin: 5px 0;"><strong>We're on our way!</strong></p>
        <p style="margin: 5px 0;"><strong>Delivery Address:</strong> {{ $order->delivery_address }}</p>
        <p style="margin: 5px 0;"><strong>Expected Time:</strong> {{ $order->requested_time->format('g:i A') }}</p>
        <p style="margin: 10px 0 5px; color: #0c5460;"><em>Please ensure someone is available to receive the delivery. We'll call when we arrive!</em></p>
    </div>
@else
    <div class="delivery-info" style="background-color: #d1ecf1; border-left: 4px solid #17a2b8;">
        <div class="info-label">🏪 Ready for Pickup!</div>
        <p style="margin: 5px 0;"><strong>Available now until:</strong> {{ $order->requested_time->format('g:i A') }}</p>
        <p style="margin: 5px 0;"><strong>Pickup Location:</strong></p>
        <p style="margin: 5px 0; padding-left: 15px;">
            KneadIt Bakery<br>
            123 Baker Street<br>
            Sweet City, SC 12345
        </p>
        <p style="margin: 10px 0 5px; color: #0c5460;"><em>Just mention your name or order number when you arrive!</em></p>
    </div>
@endif

<div style="background-color: #d4edda; border-radius: 6px; padding: 15px; margin: 20px 0; border-left: 4px solid #28a745;">
    <div class="info-label">🌟 Order Complete!</div>
    <p style="margin: 5px 0;">Everything has been prepared exactly as requested, using only the finest ingredients. We hope you love every bite!</p>
    @if($order->payment_method === 'cash')
        <p style="margin: 5px 0;"><strong>Payment:</strong> Cash payment due upon {{ $order->delivery_address ? 'delivery' : 'pickup' }}</p>
    @else
        <p style="margin: 5px 0;"><strong>Payment:</strong> Already processed via {{ ucfirst($order->payment_method) }}</p>
    @endif
</div>

<p>Thank you for choosing KneadIt Bakery! We can't wait for you to enjoy your fresh, delicious treats.</p>

<p><strong>Don't forget to share your experience!</strong> We'd love to hear how you enjoyed your order. Tag us on social media or leave a review!</p>

<p style="color: #666; font-size: 14px;">
    <em>Having any issues? Contact us immediately at (555) 123-BAKE or reply to this email.</em>
</p>
@endsection