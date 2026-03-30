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


@section('content')
    <h2 style="color: {{ $secondaryColor }}; margin: 0 0 16px; font-size: 20px;">Purchase Order</h2>

    <p style="margin: 0 0 5px;"><strong>From:</strong> {{ $storeName }}</p>
    <p style="margin: 0 0 5px;"><strong>To:</strong> {{ $supplierName }}</p>
    <p style="margin: 0 0 5px;"><strong>Date:</strong> {{ now()->format('F j, Y') }}</p>
    <p style="margin: 0 0 20px;"><strong>Requested Delivery:</strong> {{ \Carbon\Carbon::parse($requestedDate)->format('F j, Y') }}</p>

    <p style="margin: 0 0 10px; font-weight: 600;">Please supply the following items:</p>

    <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse: collapse; margin: 0 0 20px;">
        <thead>
            <tr style="background-color: #fef9ef;">
                <th style="padding: 8px 12px; text-align: left; border-bottom: 2px solid {{ $primaryColor }};">Item</th>
                <th style="padding: 8px 12px; text-align: left; border-bottom: 2px solid {{ $primaryColor }};">SKU</th>
                <th style="padding: 8px 12px; text-align: right; border-bottom: 2px solid {{ $primaryColor }};">Qty</th>
                <th style="padding: 8px 12px; text-align: right; border-bottom: 2px solid {{ $primaryColor }};">Unit Price</th>
                <th style="padding: 8px 12px; text-align: right; border-bottom: 2px solid {{ $primaryColor }};">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $item)
            <tr>
                <td style="padding: 8px 12px; border-bottom: 1px solid #e8d0b0;">{{ $item['name'] }}</td>
                <td style="padding: 8px 12px; border-bottom: 1px solid #e8d0b0;">{{ $item['sku'] ?? '—' }}</td>
                <td style="padding: 8px 12px; text-align: right; border-bottom: 1px solid #e8d0b0;">{{ $item['needed'] }} {{ $item['unit'] }}</td>
                <td style="padding: 8px 12px; text-align: right; border-bottom: 1px solid #e8d0b0;">${{ number_format($item['unit_price'], 2) }}</td>
                <td style="padding: 8px 12px; text-align: right; border-bottom: 1px solid #e8d0b0;">${{ number_format($item['subtotal'], 2) }}</td>
            </tr>
            @endforeach
            <tr>
                <td colspan="4" style="padding: 8px 12px; text-align: right; font-weight: 700;">Total:</td>
                <td style="padding: 8px 12px; text-align: right; font-weight: 700;">${{ number_format($total, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <p>Please confirm receipt of this order and estimated delivery date.</p>
    <p>Thank you for your continued partnership.</p>
    <p>Best regards,<br><strong>{{ $storeName }}</strong></p>
@endsection
