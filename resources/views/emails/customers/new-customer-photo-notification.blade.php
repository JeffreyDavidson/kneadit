@extends('emails.layout')

@php
/** @var App\Models\Customers\CustomerPhoto $photo */
/** @var string $primaryColor */
/** @var string $secondaryColor */
/** @var string $storeName */
@endphp

@section('title', 'New gallery photo')

@section('content')
    <p style="margin: 0 0 15px;">A customer just submitted a photo to the {{ $storeName }} gallery. Open your dashboard to approve or hide it.</p>

    <div style="background-color: #fef9ef; border-radius: 8px; padding: 20px; margin: 0 0 20px;">
        <div style="padding: 6px 0; border-bottom: 1px solid #e8e3d8;">
            <span style="color: #888; font-size: 13px;">From</span><br>
            <span style="color: {{ $secondaryColor }}; font-weight: 600;">{{ $photo->customer_name }}</span><br>
            <a href="mailto:{{ $photo->customer_email }}" style="color: {{ $primaryColor }};">{{ $photo->customer_email }}</a>
        </div>

        @if ($photo->caption)
            <div style="padding: 6px 0; border-bottom: 1px solid #e8e3d8;">
                <span style="color: #888; font-size: 13px;">Caption</span><br>
                <span style="color: {{ $secondaryColor }};">{{ $photo->caption }}</span>
            </div>
        @endif

        <div style="padding: 6px 0;">
            <span style="color: #888; font-size: 13px;">File</span><br>
            <span style="color: {{ $secondaryColor }}; font-family: monospace; font-size: 13px;">{{ basename($photo->photo_path) }}</span>
        </div>
    </div>

    <p style="margin: 0 0 20px; font-size: 14px; color: #555;">New photos arrive in the moderation queue and stay hidden from the public gallery until you approve them.</p>
@endsection
