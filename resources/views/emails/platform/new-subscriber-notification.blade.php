@php
/** @var string $bakerName */
/** @var string $bakerEmail */
/** @var string $storeName */
/** @var string $storefrontHost */
/** @var string $plan */
/** @var string $centralAdminUrl */
@endphp
@extends('emails.platform.layout')

@section('title', 'New KneadIt Signup')

@section('content')
    <h2 style="margin: 0 0 16px; color: #1c1410; font-size: 22px;">🎉 New Baker Signed Up!</h2>

    <x-emails.platform.panel>
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="padding: 8px 0; color: #6b4c3b; font-size: 14px; width: 120px;">Baker</td>
                <td style="padding: 8px 0; color: #1c1410; font-weight: 600; font-size: 14px;">{{ $bakerName }}</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; color: #6b4c3b; font-size: 14px;">Email</td>
                <td style="padding: 8px 0; color: #1c1410; font-size: 14px;">{{ $bakerEmail }}</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; color: #6b4c3b; font-size: 14px;">Bakery</td>
                <td style="padding: 8px 0; color: #1c1410; font-weight: 600; font-size: 14px;">{{ $storeName }}</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; color: #6b4c3b; font-size: 14px;">Subdomain</td>
                <td style="padding: 8px 0; color: #1c1410; font-size: 14px;">{{ $storefrontHost }}</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; color: #6b4c3b; font-size: 14px;">Plan</td>
                <td style="padding: 8px 0; color: #1c1410; font-weight: 600; font-size: 14px;">{{ ucfirst($plan) }}</td>
            </tr>
        </table>
    </x-emails.platform.panel>

    <x-emails.platform.button :url="$centralAdminUrl">View in Central Admin →</x-emails.platform.button>

    <p style="margin: 0; color: #6b4c3b; font-size: 14px;">KneadIt Platform Notification</p>

    <x-emails.platform.fallback-link :url="$centralAdminUrl" />
@endsection
