@extends('emails.layout')

@section('title', 'Order Confirmed - KneadIt Bakery')

@section('badge-color', '#28a745')

@section('content')
<p>Hello {{ $customer->name }},</p>

<p><strong>Great news!</strong> Your order has been confirmed and we're preparing to get started on your delicious treats!</p>

<div class="status-badge" style="background-color: #28a745;">
    ✅ Order Confirmed
</div>

<div class="order-details">
    <div class="order-number">Order #{{ $order->order_number }}</div>
    
    <div class="order-items">
        <h4 style="margin-bottom: 10px; color: #8b4513;">Your Items:</h4>
        @foreach($orderItems as $item)
            <div class="order-item">
                <div>
                    <div class="item-name">{{ $item->product->name }}</div>
                    <div class="item-details">
                        Quantity: {{ $item->quantity }}
                        @if($item->special_instructions)
                            <br><em>Note: {{ $item->special_instructions }}</em>
                        @endif
                    </div>
                </div>
                <div class="item-price">${{ number_format($item->total_price, 2) }}</div>
            </div>
        @endforeach
    </div>

    <div class="order-total">
        <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
            <span>Subtotal:</span>
            <span>${{ number_format($order->subtotal, 2) }}</span>
        </div>
        @if($order->delivery_fee > 0)
            <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                <span>Delivery Fee:</span>
                <span>${{ number_format($order->delivery_fee, 2) }}</span>
            </div>
        @endif
        <div class="divider" style="background-color: rgba(255,255,255,0.3); margin: 10px 0;"></div>
        <div style="display: flex; justify-content: space-between;">
            <span class="total-label">Total:</span>
            <span class="total-amount">${{ number_format($order->total, 2) }}</span>
        </div>
    </div>
</div>

@if($order->delivery_address)
    <div class="delivery-info">
        <div class="info-label">🚚 Delivery Details</div>
        <p style="margin: 5px 0;"><strong>Address:</strong> {{ $order->delivery_address }}</p>
        <p style="margin: 5px 0;"><strong>Requested Date:</strong> {{ $order->requested_date->format('M j, Y') }}</p>
        <p style="margin: 5px 0;"><strong>Requested Time:</strong> {{ $order->requested_time->format('g:i A') }}</p>
    </div>
@else
    <div class="delivery-info">
        <div class="info-label">🏪 Pickup Details</div>
        <p style="margin: 5px 0;"><strong>Pickup Date:</strong> {{ $order->requested_date->format('M j, Y') }}</p>
        <p style="margin: 5px 0;"><strong>Pickup Time:</strong> {{ $order->requested_time->format('g:i A') }}</p>
        <p style="margin: 5px 0;"><strong>Location:</strong> 123 Baker Street, Sweet City, SC 12345</p>
    </div>
@endif

@if($order->notes)
    <div style="background-color: #fff9e6; border-radius: 6px; padding: 15px; margin: 15px 0; border-left: 4px solid #ffc107;">
        <div class="info-label">📝 Order Notes</div>
        <p style="margin: 5px 0;">{{ $order->notes }}</p>
    </div>
@endif

<p>We'll send you another email when your order is being prepared. If you have any questions or need to make changes, please contact us as soon as possible.</p>

<p>Thank you for choosing KneadIt Bakery!</p>

<p style="color: #666; font-size: 14px;">
    <em>Payment Method: {{ ucfirst($order->payment_method) }}</em>
</p>
@endsection