@extends('emails.layout')

@php
/** @var App\Models\Inventory\ProductWaitlist $entry */
/** @var App\Models\Inventory\Product|null $product */
/** @var int $totalWaiting */
/** @var string $primaryColor */
/** @var string $secondaryColor */
/** @var string $storeName */
@endphp

@section('title', 'New waitlist signup')

@section('content')
    <p style="margin: 0 0 15px;">
        <strong>{{ $entry->customer_name ?? $entry->customer_email }}</strong> just joined the waitlist for
        <strong>{{ $product?->name ?? 'a product' }}</strong>.
    </p>

    <div style="background-color: #fef9ef; border-radius: 8px; padding: 20px; margin: 0 0 20px;">
        <div style="padding: 6px 0; border-bottom: 1px solid #e8e3d8;">
            <span style="color: #888; font-size: 13px;">Customer</span><br>
            <span style="color: {{ $secondaryColor }};">{{ $entry->customer_name ?? '—' }}</span><br>
            <a href="mailto:{{ $entry->customer_email }}" style="color: {{ $primaryColor }};">{{ $entry->customer_email }}</a>
        </div>

        <div style="padding: 6px 0;">
            <span style="color: #888; font-size: 13px;">Total waiting on this item</span><br>
            <span style="font-size: 22px; font-weight: 700; color: {{ $secondaryColor }};">{{ $totalWaiting }}</span>
        </div>
    </div>

    <p style="margin: 0 0 20px; font-size: 14px; color: #555;">When you bring this item back in stock, use the <strong>Notify Waitlist</strong> action on the product to email everyone at once.</p>
@endsection
