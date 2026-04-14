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


@section('title', 'Your Catering Quote')

@section('content')
<p style="margin: 0 0 15px;">Hello {{ $inquiry->customer_name }},</p>

<p style="margin: 0 0 20px;">Thank you for your catering inquiry! We've put together a quote for your upcoming event.</p>

<div style="background-color: #fef9ef; border-radius: 8px; padding: 20px; margin: 0 0 20px; border-left: 4px solid {{ $primaryColor }};">
    <div style="font-size: 18px; font-weight: 700; color: {{ $secondaryColor }}; margin-bottom: 15px;">🎉 Event Details</div>

    <div style="padding: 8px 0; border-bottom: 1px solid #e8e3d8;">
        <span style="color: #888; font-size: 13px;">Event Type</span><br>
        <span style="color: {{ $secondaryColor }}; font-weight: 600;">{{ $inquiry->event_type_label }}</span>
    </div>

    <div style="padding: 8px 0; border-bottom: 1px solid #e8e3d8;">
        <span style="color: #888; font-size: 13px;">Date</span><br>
        <span style="color: {{ $secondaryColor }}; font-weight: 600;">{{ $inquiry->event_date->format('l, F j, Y') }}</span>
    </div>

    <div style="padding: 8px 0; border-bottom: 1px solid #e8e3d8;">
        <span style="color: #888; font-size: 13px;">Guest Count</span><br>
        <span style="color: {{ $secondaryColor }}; font-weight: 600;">{{ $inquiry->guest_count }} guests</span>
    </div>

    @if ($inquiry->venue_address)
    <div style="padding: 8px 0; border-bottom: 1px solid #e8e3d8;">
        <span style="color: #888; font-size: 13px;">Venue</span><br>
        <span style="color: {{ $secondaryColor }}; font-weight: 600;">{{ $inquiry->venue_address }}</span>
    </div>
    @endif

    <div style="margin-top: 16px; padding-top: 12px; border-top: 2px solid {{ $primaryColor }}; text-align: center;">
        <span style="color: #888; font-size: 13px;">Your Quote</span><br>
        <span style="font-size: 28px; font-weight: 700; color: {{ $secondaryColor }};">@money($inquiry->quoted_amount)</span>
    </div>
</div>

<p style="margin: 0 0 15px;">This quote includes everything we discussed for your event. If you'd like to proceed or have any questions, simply reply to this email or give us a call.</p>

<div style="text-align: center; margin: 25px 0;">
    <p style="font-size: 14px; color: #888; margin: 0;">To confirm your order, please reply to this email or contact us directly.</p>
</div>

<p style="margin: 0 0 5px;">We look forward to making your event special! 🎂</p>
<p style="margin: 0; font-weight: 600;">— {{ $storeName }}</p>
@endsection
