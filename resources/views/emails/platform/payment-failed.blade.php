@php
/** @var \App\Models\Staff\User $user */
/** @var string|null $billingUrl */
@endphp
@extends('emails.platform.layout')

@section('title', 'Payment failed')

@section('content')
    <h2 style="margin: 0 0 16px; color: #1c1410; font-size: 22px;">Hi {{ $user->name }},</h2>

    <p style="margin: 0 0 16px;">We couldn't process your KneadIt subscription payment. Please update your payment method to keep your bakery running.</p>

    @if ($billingUrl)
        <x-emails.platform.button :url="$billingUrl">Update payment</x-emails.platform.button>
    @endif

    <p style="margin: 0; color: #6b4c3b; font-size: 14px;">— KneadIt</p>

    @if ($billingUrl)
        <x-emails.platform.fallback-link :url="$billingUrl" />
    @endif
@endsection
