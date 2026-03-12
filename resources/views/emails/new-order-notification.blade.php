@extends('emails.layout')

@section('title', 'New Order')

@section('content')
    <h2 style="color: {{ $secondaryColor }}; margin: 0 0 16px; font-size: 20px;">New Order Received!</h2>

    <div style="background: #fef9ef; border-radius: 8px; padding: 16px; margin: 16px 0; border: 1px solid #e8d0b0;">
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="padding: 6px 0; color: #6b4c3b; font-size: 14px;">Order #</td>
                <td style="padding: 6px 0; color: {{ $secondaryColor }}; font-weight: 600; text-align: right;">{{ $order->order_number }}</td>
            </tr>
            <tr>
                <td style="padding: 6px 0; color: #6b4c3b; font-size: 14px;">Customer</td>
                <td style="padding: 6px 0; color: {{ $secondaryColor }}; font-weight: 600; text-align: right;">{{ $order->customer?->name ?? 'Unknown' }}</td>
            </tr>
            <tr>
                <td style="padding: 6px 0; color: #6b4c3b; font-size: 14px;">Email</td>
                <td style="padding: 6px 0; color: {{ $secondaryColor }}; text-align: right; font-size: 14px;">{{ $order->customer?->email ?? '—' }}</td>
            </tr>
            @if($order->requested_date)
            <tr>
                <td style="padding: 6px 0; color: #6b4c3b; font-size: 14px;">Requested Date</td>
                <td style="padding: 6px 0; color: {{ $secondaryColor }}; font-weight: 600; text-align: right;">{{ $order->requested_date->format('M j, Y') }}</td>
            </tr>
            @endif
            <tr>
                <td style="padding: 6px 0; color: #6b4c3b; font-size: 14px;">Payment</td>
                <td style="padding: 6px 0; color: {{ $secondaryColor }}; text-align: right;">
                    <span style="padding: 2px 8px; border-radius: 4px; font-size: 12px; font-weight: 600;
                        background: {{ $order->payment_status === 'paid' ? '#d4f5d4' : '#fef3c7' }};
                        color: {{ $order->payment_status === 'paid' ? '#065f46' : '#92400e' }};">
                        {{ ucfirst($order->payment_status) }} ({{ ucfirst($order->payment_method) }})
                    </span>
                </td>
            </tr>
            <tr>
                <td style="padding: 6px 0; color: #6b4c3b; font-size: 14px;">Total</td>
                <td style="padding: 6px 0; color: {{ $secondaryColor }}; font-weight: 700; text-align: right; font-size: 18px;">${{ number_format($order->total, 2) }}</td>
            </tr>
        </table>
    </div>

    @if($order->orderItems->count())
    <h3 style="color: {{ $secondaryColor }}; font-size: 15px; margin: 20px 0 8px;">Items:</h3>
    <table style="width: 100%; border-collapse: collapse;">
        @foreach($order->orderItems as $item)
        <tr style="border-bottom: 1px solid #e8d0b0;">
            <td style="padding: 8px 0; color: #4a3728; font-size: 14px;">
                {{ $item->product?->name ?? 'Item' }} × {{ $item->quantity }}
                @if($item->special_instructions)
                    <br><span style="color: #8b6844; font-size: 12px; font-style: italic;">{{ $item->special_instructions }}</span>
                @endif
            </td>
            <td style="padding: 8px 0; color: {{ $secondaryColor }}; font-weight: 600; text-align: right; font-size: 14px;">
                ${{ number_format($item->unit_price * $item->quantity, 2) }}
            </td>
        </tr>
        @endforeach
    </table>
    @endif

    @if($order->notes)
    <div style="margin: 16px 0; padding: 12px; background: #fff7ed; border-left: 3px solid {{ $primaryColor }}; border-radius: 4px;">
        <strong style="color: {{ $secondaryColor }}; font-size: 13px;">Customer Notes:</strong>
        <p style="color: #4a3728; font-size: 13px; margin: 4px 0 0;">{{ $order->notes }}</p>
    </div>
    @endif

    <div style="text-align: center; margin: 24px 0 0;">
        <a href="{{ url('/admin') }}" style="display: inline-block; background: {{ $primaryColor }}; color: #ffffff; padding: 12px 28px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 14px;">
            View in Dashboard →
        </a>
    </div>
@endsection
