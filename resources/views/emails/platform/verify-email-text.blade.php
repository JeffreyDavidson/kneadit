@php
/** @var string $ownerName */
/** @var string $verificationUrl */
/** @var int $expiresInMinutes */
@endphp
Hi {{ $ownerName }},

Welcome to KneadIt! Please confirm your email address so you can set up your bakery.

Verify email: {!! $verificationUrl !!}

This link expires in {{ $expiresInMinutes }} minutes. If it has expired, sign in and choose Resend to get a new one.

If you didn't create a KneadIt account, you can ignore this email.

KneadIt
The bakery management platform for cottage food bakers
