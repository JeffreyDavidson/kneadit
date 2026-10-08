@php
/** @var array<int, int> $userIds */
@endphp
@extends('emails.platform.layout')

@section('title', 'Paying accounts without a bakery')

@section('content')
    <h2 style="margin: 0 0 16px; color: #1c1410; font-size: 22px;">Paying accounts without a bakery</h2>

    <p style="margin: 0 0 16px;">
        These platform accounts have an active, trialing or past due subscription but no bakery linked to them. Someone
        is being billed without a bakery to use. Look each one up by id in the central admin and in Stripe.
    </p>

    <p style="margin: 0 0 16px; font-family: monospace; font-size: 14px;">
        User ids: {{ implode(', ', $userIds) }}
    </p>
@endsection
