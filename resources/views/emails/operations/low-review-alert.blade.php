@extends('emails.layout')

@php
/** @var App\Models\Engagement\Review $review */
/** @var string $primaryColor */
/** @var string $secondaryColor */
/** @var string $storeName */
@endphp

@section('title', 'Low-rating review received')

@section('content')
    <p style="margin: 0 0 15px;">A customer just left a {{ $review->rating }}-star review on {{ $storeName }}. You'll probably want to reach out and make it right.</p>

    <div style="background-color: #fef9ef; border-radius: 8px; padding: 20px; margin: 0 0 20px;">
        <p style="margin: 0 0 8px; font-size: 13px; color: #555; text-transform: uppercase; letter-spacing: 1px;">Rating</p>
        <p style="margin: 0 0 12px; font-size: 22px; font-weight: 700; color: {{ $secondaryColor }};">
            {{ str_repeat('★', $review->rating) }}{{ str_repeat('☆', max(0, 5 - $review->rating)) }}
        </p>

        @if ($review->comment)
            <p style="margin: 0 0 6px; font-size: 13px; color: #555; text-transform: uppercase; letter-spacing: 1px;">Comment</p>
            <p style="margin: 0 0 12px; color: {{ $secondaryColor }};">{{ $review->comment }}</p>
        @endif

        <p style="margin: 0 0 6px; font-size: 13px; color: #555; text-transform: uppercase; letter-spacing: 1px;">Customer</p>
        <p style="margin: 0; color: {{ $secondaryColor }};">
            {{ $review->customer_name }}<br>
            <a href="mailto:{{ $review->customer_email }}" style="color: {{ $primaryColor }};">{{ $review->customer_email }}</a>
        </p>
    </div>

    <p style="margin: 0 0 20px; font-size: 14px; color: #555;">A quick personal email or call within 24 hours dramatically improves the chance the customer comes back. The review is in your dashboard under Reviews — moderate or feature it from there.</p>
@endsection
