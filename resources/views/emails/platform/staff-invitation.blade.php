@php
/** @var string $storeName */
/** @var string $role */
/** @var string $acceptUrl */
/** @var string $expiresAt */
@endphp
@extends('emails.platform.layout')

@section('title', "You've been invited to join {$storeName}")

@section('content')
    <h2 style="margin: 0 0 16px; color: #1c1410; font-size: 22px;">You've been invited!</h2>

    <p style="margin: 0 0 16px;">
        You've been invited to join <strong>{{ $storeName }}</strong> on KneadIt as a <strong>{{ $role }}</strong>.
    </p>

    <x-emails.platform.button :url="$acceptUrl">Accept Invitation</x-emails.platform.button>

    <p style="margin: 0; color: #6b4c3b; font-size: 14px;">This invitation expires on {{ $expiresAt }}.</p>

    <x-emails.platform.fallback-link :url="$acceptUrl" />
@endsection
