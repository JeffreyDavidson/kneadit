@extends('emails.layout')

@section('title', "New Message on Order #{$order->order_number}")

@section('content')
    <h2 style="margin: 0 0 15px; font-size: 22px; color: #1c1410;">
        💬 New Message on Order #{{ $order->order_number }}
    </h2>

    <p style="margin: 0 0 20px; color: #78350f;">
        <strong>{{ $orderMessage->sender_name }}</strong> sent a message:
    </p>

    <div style="background-color: #fef9ef; border-left: 4px solid #d4920c; padding: 15px 20px; border-radius: 0 8px 8px 0; margin: 0 0 25px;">
        <p style="margin: 0; color: #1c1410; white-space: pre-wrap;">{{ $orderMessage->message }}</p>
    </div>

    <p style="margin: 0 0 10px; color: #78350f; font-size: 14px;">
        @if($orderMessage->sender_type === 'customer')
            Log in to your admin panel to reply.
        @else
            Visit your order tracking page to view and reply.
        @endif
    </p>
@endsection
