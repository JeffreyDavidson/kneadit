@php
/** @var string $bakerName */
/** @var string $storeName */
/** @var string $adminUrl */
/** @var string $plan */
/** @var string $trialEndsAt */
@endphp
@extends('emails.platform.layout')

@section('title', 'Welcome to KneadIt')

@section('content')
    <h2 style="margin: 0 0 16px; color: #1c1410; font-size: 22px;">Welcome, {{ $bakerName }}! 🎉</h2>

    <p style="margin: 0 0 16px;">
        <strong>{{ $storeName }}</strong> is all set up and ready to go. Here's what you need to know:
    </p>

    <x-emails.platform.panel>
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="padding: 6px 0; color: #6b4c3b; font-size: 14px;">Plan</td>
                <td style="padding: 6px 0; color: #1c1410; font-weight: 600; text-align: right; font-size: 14px;">{{ ucfirst($plan) }}</td>
            </tr>
            <tr>
                <td style="padding: 6px 0; color: #6b4c3b; font-size: 14px;">Free trial until</td>
                <td style="padding: 6px 0; color: #1c1410; font-weight: 600; text-align: right; font-size: 14px;">{{ $trialEndsAt }}</td>
            </tr>
        </table>
    </x-emails.platform.panel>

    <x-emails.platform.button :url="$adminUrl">Go to Your Dashboard →</x-emails.platform.button>

    <h3 style="margin: 24px 0 12px; color: #1c1410; font-size: 16px;">Getting started:</h3>
    <ol style="margin: 0; padding-left: 20px; color: #4a3728; font-size: 14px; line-height: 2;">
        <li>Complete the onboarding wizard to set up your store</li>
        <li>Add your products and categories</li>
        <li>Configure your business hours and delivery options</li>
        <li>Connect a payment method (Stripe or PayPal)</li>
        <li>Share your storefront link with customers!</li>
    </ol>

    <p style="margin: 24px 0 0; color: #6b4c3b; font-size: 14px;">Questions? Just reply to this email — we're here to help.</p>

    <x-emails.platform.fallback-link :url="$adminUrl" />
@endsection
