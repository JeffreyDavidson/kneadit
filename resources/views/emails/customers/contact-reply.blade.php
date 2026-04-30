@extends('emails.layout')

@php
    /** @var string $replyBody */
    /** @var string $originalSubject */
    /** @var string $originalMessage */
    /** @var \Carbon\Carbon $originalSentAt */
    /** @var string $customerName */
    /** @var string $secondaryColor */
@endphp

@section('content')
    <p style="color: {{ $secondaryColor }}; font-size: 16px; line-height: 1.6; margin: 0 0 16px;">
        Hi {{ $customerName }},
    </p>

    <div style="color: {{ $secondaryColor }}; font-size: 16px; line-height: 1.6; white-space: pre-wrap;">{{ $replyBody }}</div>

    <hr style="margin: 32px 0 16px; border: none; border-top: 1px solid #e0d4c1;">

    <p style="color: #a89585; font-size: 12px; margin: 0 0 8px;">
        On {{ $originalSentAt->format('M j, Y \a\t g:i A') }}, you wrote:
    </p>
    <blockquote style="color: #a89585; font-size: 13px; border-left: 3px solid #e0d4c1; padding: 4px 12px; margin: 0; white-space: pre-wrap;">{{ $originalMessage }}</blockquote>
@endsection
