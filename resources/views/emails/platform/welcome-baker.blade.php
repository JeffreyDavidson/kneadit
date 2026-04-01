@php
/** @var string $storeName */
/** @var string $primaryColor */
/** @var string $secondaryColor */
/** @var string $storeEmail */
/** @var string $storePhone */
/** @var string $storeAddress */
/** @var string|null $logoUrl */
/** @var string $bakerName */
/** @var string $adminUrl */
/** @var string $plan */
/** @var string $trialEndsAt */
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Welcome to KneadIt</title>
</head>
<body style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #1c1410; background-color: #fef9ef; margin: 0; padding: 20px;">
    <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">

        {{-- Header --}}
        <div style="background: linear-gradient(135deg, #1c1410 0%, #2a1f18 100%); text-align: center; padding: 40px 20px;">
            <h1 style="margin: 0; font-size: 32px; font-weight: 700; color: #d4920c;">KneadIt</h1>
            <p style="margin: 8px 0 0; color: #d4a574; font-size: 14px;">Your bakery management platform</p>
        </div>

        {{-- Content --}}
        <div style="padding: 32px 40px;">
            <h2 style="margin: 0 0 16px; color: #1c1410; font-size: 22px;">Welcome, {{ $bakerName }}! 🎉</h2>

            <p style="margin: 0 0 16px; color: #4a3728;">
                <strong>{{ $storeName }}</strong> is all set up and ready to go. Here's what you need to know:
            </p>

            {{-- Quick Stats --}}
            <div style="background: #fef9ef; border-radius: 8px; padding: 20px; margin: 20px 0; border: 1px solid #e8d0b0;">
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
            </div>

            {{-- CTA --}}
            <div style="text-align: center; margin: 28px 0;">
                <a href="{{ $adminUrl }}" style="display: inline-block; background: #d4920c; color: #ffffff; padding: 14px 32px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 16px;">
                    Go to Your Dashboard →
                </a>
            </div>

            {{-- Next Steps --}}
            <h3 style="margin: 24px 0 12px; color: #1c1410; font-size: 16px;">Getting started:</h3>
            <ol style="margin: 0; padding-left: 20px; color: #4a3728; font-size: 14px; line-height: 2;">
                <li>Complete the onboarding wizard to set up your store</li>
                <li>Add your products and categories</li>
                <li>Configure your business hours and delivery options</li>
                <li>Connect a payment method (Stripe or PayPal)</li>
                <li>Share your storefront link with customers!</li>
            </ol>

            <p style="margin: 24px 0 0; color: #6b4c3b; font-size: 14px;">
                Questions? Just reply to this email — we're here to help.
            </p>
        </div>

        {{-- Footer --}}
        <div style="background: #1c1410; color: #fef9ef; text-align: center; padding: 24px 20px; font-size: 13px;">
            <p style="margin: 0 0 4px; font-weight: 600; color: #d4920c;">KneadIt</p>
            <p style="margin: 0; opacity: 0.6;">The bakery management platform for cottage food bakers</p>
        </div>
    </div>
</body>
</html>
