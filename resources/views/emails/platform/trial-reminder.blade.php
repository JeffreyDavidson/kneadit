@php
/** @var \App\Models\Staff\User $user */
/** @var string $storeName */
/** @var int $daysLeft */
/** @var string|null $billingUrl */
@endphp
@extends('emails.platform.layout')

@section('title', 'Your KneadIt trial is ending')

@section('content')
    <h2 style="margin: 0 0 16px; color: #1c1410; font-size: 22px;">Hi {{ $user->name }},</h2>

    <p style="margin: 0 0 16px;">Your KneadIt free trial for {{ $storeName }} ends {{ $daysLeft === 1 ? 'tomorrow' : "in {$daysLeft} days" }}.</p>

    @if ($billingUrl)
        <p style="margin: 0 0 16px;">Subscribe now to keep your bakery running without interruption:</p>

        <x-emails.platform.button :url="$billingUrl">Subscribe now</x-emails.platform.button>
    @endif

    @if ($daysLeft <= 3)
        <p style="margin: 0 0 16px;">After your trial expires, your storefront will be paused until you subscribe.</p>
    @endif

    <p style="margin: 0 0 16px; color: #6b4c3b; font-size: 14px;">Questions? Just reply to this email.</p>

    <p style="margin: 0; color: #6b4c3b; font-size: 14px;">— The KneadIt Team</p>

    @if ($billingUrl)
        <x-emails.platform.fallback-link :url="$billingUrl" />
    @endif
@endsection
