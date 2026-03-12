@extends('emails.layout')

@section('title', 'Order Confirmation')

@section('content')
    <h2 style="color: {{ $secondaryColor }}; margin: 0 0 16px; font-size: 20px;">Thanks for your order! 🎉</h2>

    <p style="color: #4a3728; margin: 0 0 16px;">
        Hi {{ $order->customer?->name ?? 'there' }}, your order has been received and is being reviewed.
    </p>

    <div style="background: #fef9ef; border-radius: 8px; padding: 16px; margin: 16px 0; border: 1px solid #e8d0b0;">
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="padding: 6px 0; color: #6b4c3b; font-size: 14px;">Order #</td>
                <td style="padding: 6px 0; color: {{ $secondaryColor }}; font-weight: 600; text-align: right;">{{ $order->order_number }}</td>
            </tr>
            @if($order->requested_date)
            <tr>
                <td style="padding: 6px 0; color: #6b4c3b; font-size: 14px;">Requested Date</td>
                <td style="padding: 6px 0; color: {{ $secondaryColor }}; font-weight: 600; text-align: right;">{{ $order->requested_date->format('M j, Y') }}</td>
            </tr>
            @endif
            <tr>
                <td style="padding: 6px 0; color: #6b4c3b; font-size: 14px;">Total</td>
                <td style="padding: 6px 0; color: {{ $secondaryColor }}; font-weight: 700; text-align: right; font-size: 18px;">${{ number_format($order->total, 2) }}</td>
            </tr>
        </table>
    </div>

    @if($order->orderItems->count())
    <h3 style="color: {{ $secondaryColor }}; font-size: 15px; margin: 20px 0 8px;">Items Ordered:</h3>
    <table style="width: 100%; border-collapse: collapse;">
        @foreach($order->orderItems as $item)
        <tr style="border-bottom: 1px solid #e8d0b0;">
            <td style="padding: 8px 0; color: #4a3728; font-size: 14px;">
                {{ $item->product?->name ?? 'Item' }} × {{ $item->quantity }}
            </td>
            <td style="padding: 8px 0; color: {{ $secondaryColor }}; font-weight: 600; text-align: right; font-size: 14px;">
                ${{ number_format($item->unit_price * $item->quantity, 2) }}
            </td>
        </tr>
        @endforeach
    </table>
    @endif

    <p style="color: #6b4c3b; font-size: 13px; margin: 20px 0 0;">
        We'll send you an update when your order status changes. If you have questions, reply to this email.
    </p>
@endsection
