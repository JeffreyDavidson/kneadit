@php
/** @var string $body */
/** @var string $emailSubject */
/** @var string|null $bakerName */
/** @var string $adminUrl */
/** @var string $helpUrl */
@endphp
@extends('emails.platform.layout')

@section('title', $emailSubject)

@section('content')
    @if ($bakerName)
        <h2 style="margin: 0 0 16px; color: #1c1410; font-size: 22px;">Hi {{ $bakerName }},</h2>
    @endif

    <div style="margin: 0 0 16px; white-space: pre-wrap;">{!! clean($body) !!}</div>

    <x-emails.platform.button :url="$adminUrl">Open Your Dashboard →</x-emails.platform.button>

    <p style="margin: 0 0 16px; color: #6b4c3b; font-size: 14px;">You're receiving this as part of onboarding for your KneadIt bakery.</p>

    <p style="margin: 0; color: #6b4c3b; font-size: 14px;">
        Questions? Reply to this email or visit
        <a href="{{ $helpUrl }}" style="color: #8a5a0a;">the Help Center</a>.
    </p>

    <x-emails.platform.fallback-link :url="$adminUrl" />
@endsection
