@extends('emails.layout')

@php
/** @var App\Models\Customers\CateringInquiry $inquiry */
/** @var string $primaryColor */
/** @var string $secondaryColor */
/** @var string $storeName */
@endphp

@section('title', 'New catering inquiry')

@section('content')
    <p style="margin: 0 0 15px;">A new catering inquiry just came in for {{ $storeName }}. The faster you reply with a quote, the more likely the customer books with you.</p>

    <div style="background-color: #fef9ef; border-radius: 8px; padding: 20px; margin: 0 0 20px;">
        <div style="padding: 6px 0; border-bottom: 1px solid #e8e3d8;">
            <span style="color: #888; font-size: 13px;">Customer</span><br>
            <span style="color: {{ $secondaryColor }}; font-weight: 600;">{{ $inquiry->customer_name }}</span><br>
            <a href="mailto:{{ $inquiry->customer_email }}" style="color: {{ $primaryColor }};">{{ $inquiry->customer_email }}</a>
            @if ($inquiry->customer_phone)
                <span style="color: #888;"> · {{ $inquiry->customer_phone }}</span>
            @endif
        </div>

        <div style="padding: 6px 0; border-bottom: 1px solid #e8e3d8;">
            <span style="color: #888; font-size: 13px;">Event</span><br>
            <span style="color: {{ $secondaryColor }};">{{ $inquiry->event_type }}</span>
            @if ($inquiry->event_date)
                <span style="color: #888;"> · {{ \Carbon\Carbon::parse($inquiry->event_date)->format('l, F j, Y') }}</span>
            @endif
        </div>

        <div style="padding: 6px 0; border-bottom: 1px solid #e8e3d8;">
            <span style="color: #888; font-size: 13px;">Guests</span><br>
            <span style="color: {{ $secondaryColor }};">{{ $inquiry->guest_count ?? '—' }}</span>
            @if ($inquiry->budget)
                <span style="color: #888;"> · Budget @money($inquiry->budget)</span>
            @endif
        </div>

        @if ($inquiry->venue_address)
            <div style="padding: 6px 0; border-bottom: 1px solid #e8e3d8;">
                <span style="color: #888; font-size: 13px;">Venue</span><br>
                <span style="color: {{ $secondaryColor }};">{{ $inquiry->venue_address }}</span>
            </div>
        @endif

        @if ($inquiry->details)
            <div style="padding: 6px 0;">
                <span style="color: #888; font-size: 13px;">Details</span><br>
                <span style="color: {{ $secondaryColor }};">{{ $inquiry->details }}</span>
            </div>
        @endif

        @if ($inquiry->dietary_requirements)
            <div style="padding: 6px 0; border-top: 1px solid #e8e3d8;">
                <span style="color: #888; font-size: 13px;">Dietary requirements</span><br>
                <span style="color: {{ $secondaryColor }};">{{ $inquiry->dietary_requirements }}</span>
            </div>
        @endif
    </div>

    <p style="margin: 0 0 20px; font-size: 14px; color: #555;">Open the inquiry in your dashboard to add a quoted amount and send the quote.</p>
@endsection
