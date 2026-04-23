@extends('emails.layout')

@php
/** @var App\Models\Customers\ContactMessage $message */
/** @var string $primaryColor */
/** @var string $secondaryColor */
/** @var string $storeName */
@endphp

@section('title', 'New contact message')

@section('content')
    <p style="margin: 0 0 15px;">A new message just came in via the {{ $storeName }} contact form.</p>

    <div style="background-color: #fef9ef; border-radius: 8px; padding: 20px; margin: 0 0 20px;">
        <div style="padding: 6px 0; border-bottom: 1px solid #e8e3d8;">
            <span style="color: #888; font-size: 13px;">From</span><br>
            <span style="color: {{ $secondaryColor }}; font-weight: 600;">{{ $message->name }}</span><br>
            <a href="mailto:{{ $message->email }}" style="color: {{ $primaryColor }};">{{ $message->email }}</a>
            @if (! empty($message->phone))
                <span style="color: #888;"> · {{ $message->phone }}</span>
            @endif
        </div>

        @if ($message->subject)
            <div style="padding: 6px 0; border-bottom: 1px solid #e8e3d8;">
                <span style="color: #888; font-size: 13px;">Subject</span><br>
                <span style="color: {{ $secondaryColor }};">{{ $message->subject }}</span>
            </div>
        @endif

        <div style="padding: 6px 0;">
            <span style="color: #888; font-size: 13px;">Message</span><br>
            <p style="margin: 6px 0 0; color: {{ $secondaryColor }}; white-space: pre-wrap;">{{ $message->message }}</p>
        </div>
    </div>

    <p style="margin: 0 0 20px; font-size: 14px; color: #555;">Hit Reply on this email to respond directly to the customer.</p>
@endsection
