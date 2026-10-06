@php
/** @var \App\Models\Staff\User $user */
/** @var \App\Models\Platform\Tenant|null $tenant */
/** @var float $amount */
@endphp
@extends('emails.platform.layout')

@section('title', 'Payment failed')

@section('content')
    <h2 style="margin: 0 0 16px; color: #1c1410; font-size: 22px;">Payment failed for {{ $user->name }} ({{ $user->email }})</h2>

    @if ($tenant)
        <p style="margin: 0 0 8px;">Tenant: {{ $tenant->store_name }} ({{ $tenant->id }})</p>
    @endif

    <p style="margin: 0;">Amount: {{ \Illuminate\Support\Number::currency($amount) }}</p>
@endsection
