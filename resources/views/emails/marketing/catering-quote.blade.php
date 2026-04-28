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


@section('title', 'Your Catering Quote')

@section('content')
<p style="margin: 0 0 15px;">Hello {{ $inquiry->customer_name }},</p>

<p style="margin: 0 0 20px;">Thank you for your catering inquiry! We've put together a quote for your upcoming event.</p>

<div style="background-color: #fef9ef; border-radius: 8px; padding: 20px; margin: 0 0 20px; border-left: 4px solid {{ $primaryColor }};">
    <div style="font-size: 18px; font-weight: 700; color: {{ $secondaryColor }}; margin-bottom: 15px;">🎉 Event Details</div>

    <div style="padding: 8px 0; border-bottom: 1px solid #e8e3d8;">
        <span style="color: #888; font-size: 13px;">Event Type</span><br>
        <span style="color: {{ $secondaryColor }}; font-weight: 600;">{{ $inquiry->event_type }}</span>
    </div>

    <div style="padding: 8px 0; border-bottom: 1px solid #e8e3d8;">
        <span style="color: #888; font-size: 13px;">Date</span><br>
        <span style="color: {{ $secondaryColor }}; font-weight: 600;">{{ $inquiry->event_date->format('l, F j, Y') }}</span>
    </div>

    <div style="padding: 8px 0; border-bottom: 1px solid #e8e3d8;">
        <span style="color: #888; font-size: 13px;">Guest Count</span><br>
        <span style="color: {{ $secondaryColor }}; font-weight: 600;">{{ $inquiry->guest_count }} guests</span>
    </div>

    @if ($inquiry->venue_address)
    <div style="padding: 8px 0; border-bottom: 1px solid #e8e3d8;">
        <span style="color: #888; font-size: 13px;">Venue</span><br>
        <span style="color: {{ $secondaryColor }}; font-weight: 600;">{{ $inquiry->venue_address }}</span>
    </div>
    @endif

    @if ($inquiry->items->isNotEmpty())
        <div style="margin-top: 16px; padding-top: 12px; border-top: 1px solid #e8e3d8;">
            <span style="color: #888; font-size: 13px;">What's included</span>
            <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="margin-top: 8px; border-collapse: collapse;">
                <thead>
                    <tr>
                        <th align="left" style="padding: 6px 4px; border-bottom: 1px solid #e8e3d8; color: #888; font-size: 12px; font-weight: 600; text-transform: uppercase;">Item</th>
                        <th align="right" style="padding: 6px 4px; border-bottom: 1px solid #e8e3d8; color: #888; font-size: 12px; font-weight: 600; text-transform: uppercase;">Qty</th>
                        <th align="right" style="padding: 6px 4px; border-bottom: 1px solid #e8e3d8; color: #888; font-size: 12px; font-weight: 600; text-transform: uppercase;">Unit</th>
                        <th align="right" style="padding: 6px 4px; border-bottom: 1px solid #e8e3d8; color: #888; font-size: 12px; font-weight: 600; text-transform: uppercase;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($inquiry->items as $item)
                        <tr>
                            <td align="left" style="padding: 8px 4px; border-bottom: 1px solid #f1ede4; color: {{ $secondaryColor }}; font-weight: 600; font-size: 14px;">
                                {{ $item->name }}
                                @if ($item->special_instructions)
                                    <div style="color: #888; font-size: 12px; font-style: italic; font-weight: 400; margin-top: 2px;">{{ $item->special_instructions }}</div>
                                @endif
                            </td>
                            <td align="right" style="padding: 8px 4px; border-bottom: 1px solid #f1ede4; color: {{ $secondaryColor }}; font-size: 14px;">{{ $item->quantity }}</td>
                            <td align="right" style="padding: 8px 4px; border-bottom: 1px solid #f1ede4; color: #555; font-size: 14px;">@money($item->unit_price)</td>
                            <td align="right" style="padding: 8px 4px; border-bottom: 1px solid #f1ede4; color: {{ $secondaryColor }}; font-weight: 600; font-size: 14px;">@money($item->line_total)</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <div style="margin-top: 16px; padding-top: 12px; border-top: 2px solid {{ $primaryColor }}; text-align: center;">
        <span style="color: #888; font-size: 13px;">Your Quote</span><br>
        <span style="font-size: 28px; font-weight: 700; color: {{ $secondaryColor }};">@money($inquiry->quoted_amount)</span>
    </div>

    @php
        $depositPercent = (int) (app(\App\Services\Settings\TenantSettings::class)->catering->depositPercent ?? 0);
        $depositAmount = $depositPercent > 0 && $inquiry->quoted_amount
            ? round($inquiry->quoted_amount->dollars() * $depositPercent / 100, 2)
            : 0;
    @endphp
    @if ($depositAmount > 0)
        <div style="margin-top: 16px; padding: 12px; background-color: #fff; border: 1px dashed {{ $primaryColor }}; border-radius: 6px; text-align: center;">
            <span style="color: #888; font-size: 13px;">Deposit to confirm ({{ $depositPercent }}%)</span><br>
            <span style="font-size: 22px; font-weight: 700; color: {{ $secondaryColor }};">${{ number_format($depositAmount, 2) }}</span>
        </div>
    @endif
</div>

<p style="margin: 0 0 15px;">This quote includes everything we discussed for your event. If you'd like to proceed or have any questions, simply reply to this email or give us a call.</p>

@php
    $stripeReader = app(\App\Services\Stripe\StripeSettingsReader::class);
    $depositPayUrl = ($depositAmount > 0 && $stripeReader->isEnabled())
        ? \Illuminate\Support\Facades\URL::temporarySignedRoute(
            'catering.payDeposit',
            now()->addDays(30),
            ['inquiry' => $inquiry->id],
        )
        : null;
@endphp

@if ($depositPayUrl)
    <div style="text-align: center; margin: 25px 0;">
        <a href="{{ $depositPayUrl }}" style="display: inline-block; padding: 14px 28px; background-color: {{ $primaryColor }}; color: #fff; text-decoration: none; border-radius: 8px; font-weight: 600;">
            Pay deposit (${{ number_format($depositAmount, 2) }})
        </a>
        <p style="margin: 12px 0 0; font-size: 12px; color: #888;">Secure payment via Stripe — link valid for 30 days.</p>
    </div>
@else
    <div style="text-align: center; margin: 25px 0;">
        <p style="font-size: 14px; color: #888; margin: 0;">To confirm your booking, please reply to this email or contact us directly to arrange the deposit.</p>
    </div>
@endif

<p style="margin: 0 0 5px;">We look forward to making your event special! 🎂</p>
<p style="margin: 0; font-weight: 600;">— {{ $storeName }}</p>
@endsection
