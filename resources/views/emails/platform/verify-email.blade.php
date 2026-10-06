@php
/** @var string $ownerName */
/** @var string $verificationUrl */
/** @var int $expiresInMinutes */
@endphp
@extends('emails.platform.layout')

@section('title', 'Verify your KneadIt email')

@section('content')
    <h2 style="margin: 0 0 16px; color: #1c1410; font-size: 22px;">Hi {{ $ownerName }},</h2>

    <p style="margin: 0 0 16px;">Welcome to KneadIt! Please confirm your email address so you can set up your bakery.</p>

    <x-emails.platform.button :url="$verificationUrl">Verify email</x-emails.platform.button>

    <p style="margin: 0 0 16px; color: #6b4c3b; font-size: 14px;">This link expires in {{ $expiresInMinutes }} minutes. If it has expired, sign in and choose Resend to get a new one.</p>

    <p style="margin: 0; color: #6b4c3b; font-size: 14px;">If you didn't create a KneadIt account, you can ignore this email.</p>

    <x-emails.platform.fallback-link :url="$verificationUrl" />
@endsection
