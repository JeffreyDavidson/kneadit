@extends('emails.layout')

@php
/** @var string $storeName */
/** @var string $primaryColor */
/** @var string $secondaryColor */
/** @var string $storeEmail */
/** @var string $storePhone */
/** @var string $storeAddress */
/** @var string|null $logoUrl */
@endphp


@section('title', 'Your Order Link')

@section('content')
<p style="margin: 0 0 15px;">Hello {{ $customer->name }},</p>

<p style="margin: 0 0 20px;">Use the button below to view your orders with {{ $storeName }}. The link works for {{ $lifetimeMinutes }} minutes.</p>

<div style="text-align: center; margin: 25px 0;">
    <a href="{{ $trackingUrl }}" style="display: inline-block; background-color: {{ $primaryColor }}; color: #ffffff; text-decoration: none; padding: 14px 32px; border-radius: 8px; font-weight: 600; font-size: 16px;">View My Orders</a>
</div>

<p style="margin: 0; color: #555; font-size: 14px;">If you didn't ask for this link, you can ignore this email.</p>
@endsection
