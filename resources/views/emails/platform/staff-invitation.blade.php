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


@section('content')
    <h2 style="color: #333; margin-top: 0;">You've been invited!</h2>
    <p style="color: #555; font-size: 16px; line-height: 1.6;">
        You've been invited to join <strong>{{ $storeName }}</strong> on KneadIt as a <strong>{{ $role }}</strong>.
    </p>
    <div style="text-align: center; margin: 32px 0;">
        <a href="{{ $acceptUrl }}" style="display: inline-block; background: {{ $primaryColor }}; color: white; text-decoration: none; padding: 14px 32px; border-radius: 8px; font-size: 16px; font-weight: 600;">
            Accept Invitation
        </a>
    </div>
    <p style="color: #888; font-size: 14px;">
        This invitation expires on {{ $expiresAt }}.
    </p>
@endsection
