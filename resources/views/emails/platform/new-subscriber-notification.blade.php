@php
/** @var string $storeName */
/** @var string $primaryColor */
/** @var string $secondaryColor */
/** @var string $storeEmail */
/** @var string $storePhone */
/** @var string $storeAddress */
/** @var string|null $logoUrl */
/** @var string $bakerName */
/** @var string $bakerEmail */
/** @var string $storefrontHost */
/** @var string $plan */
/** @var string $centralAdminUrl */
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>New KneadIt Signup</title>
</head>
<body style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #1c1410; background-color: #fef9ef; margin: 0; padding: 20px;">
    <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">

        {{-- Header --}}
        <div style="background: linear-gradient(135deg, #065f46 0%, #047857 100%); text-align: center; padding: 32px 20px;">
            <h1 style="margin: 0; font-size: 24px; font-weight: 700; color: #ffffff;">🎉 New Baker Signed Up!</h1>
        </div>

        {{-- Content --}}
        <div style="padding: 32px 40px;">
            <div style="background: #fef9ef; border-radius: 8px; padding: 20px; border: 1px solid #e8d0b0;">
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
            </div>

            <div style="text-align: center; margin: 24px 0 0;">
                <a href="{{ $centralAdminUrl }}" style="display: inline-block; background: #d4920c; color: #ffffff; padding: 12px 28px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 14px;">
                    View in Central Admin →
                </a>
            </div>
        </div>

        {{-- Footer --}}
        <div style="background: #1c1410; color: #fef9ef; text-align: center; padding: 20px; font-size: 12px;">
            <p style="margin: 0; opacity: 0.6;">KneadIt Platform Notification</p>
        </div>
    </div>
</body>
</html>
