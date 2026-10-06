@php
/** @var string $senderName */
/** @var string $senderEmail */
/** @var string $body */
@endphp
@extends('emails.platform.layout')

@section('title', 'KneadIt Contact Form')

@section('content')
    <h2 style="margin: 0 0 16px; color: #1c1410; font-size: 22px;">New contact form submission</h2>

    <p style="margin: 0 0 16px;"><strong>From:</strong> {{ $senderName }} &lt;{{ $senderEmail }}&gt;</p>

    <hr style="border: none; border-top: 1px solid #e8d0b0; margin: 20px 0;">

    <p style="margin: 0; white-space: pre-wrap;">{{ $body }}</p>
@endsection
