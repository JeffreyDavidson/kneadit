@php
/** @var \App\Models\Staff\User $user */
/** @var string $adminUrl */
/** @var string|null $billingUrl */
@endphp
@extends('emails.platform.layout')

@section('title', 'Your KneadIt trial has expired')

@section('content')
    <h2 style="margin: 0 0 16px; color: #1c1410; font-size: 22px;">Hi {{ $user->name }},</h2>

    <p style="margin: 0 0 16px;">Your KneadIt free trial has expired. Your storefront has been paused.</p>

    <p style="margin: 0 0 16px;">Don't worry — your data is safe. Subscribe to reactivate:</p>

    <x-emails.platform.button :url="$billingUrl ?? $adminUrl">Subscribe</x-emails.platform.button>

    <p style="margin: 0 0 16px;">
        Your admin panel is still accessible at:
        <a href="{{ $adminUrl }}" style="color: #8a5a0a;">{{ $adminUrl }}</a>
    </p>

    <p style="margin: 0; color: #6b4c3b; font-size: 14px;">— The KneadIt Team</p>

    <x-emails.platform.fallback-link :url="$billingUrl ?? $adminUrl" />
@endsection
