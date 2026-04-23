@extends('emails.layout')

@php
/** @var App\Models\Customers\CustomerReferral $referral */
/** @var App\Models\Financial\Coupon $coupon */
/** @var string $primaryColor */
/** @var string $secondaryColor */
@endphp

@section('title', 'Thanks for the referral')

@section('content')
    <p style="margin: 0 0 15px;">Hi {{ $referral->referrer->name }},</p>

    <p style="margin: 0 0 20px;">Great news — <strong>{{ $referral->referred->name ?? 'a friend' }}</strong> just placed their first order using your referral link. Thanks for spreading the word!</p>

    <div style="background-color: #fef9ef; border-radius: 8px; padding: 24px; margin: 0 0 20px; text-align: center;">
        <p style="margin: 0 0 6px; font-size: 13px; color: #555; text-transform: uppercase; letter-spacing: 1px;">Your reward coupon</p>
        <p style="margin: 0 0 10px; font-size: 28px; font-weight: 700; letter-spacing: 2px; color: {{ $secondaryColor }};">{{ $coupon->code }}</p>
        <p style="margin: 0; font-size: 14px; color: #555;">Worth @money($coupon->fixed_amount) off your next order. Expires {{ $coupon->expires_at?->format('M j, Y') }}.</p>
    </div>

    <p style="margin: 0 0 20px; font-size: 14px; color: #555;">Apply it at checkout the next time you order. Keep sharing your link to earn more rewards!</p>
@endsection
