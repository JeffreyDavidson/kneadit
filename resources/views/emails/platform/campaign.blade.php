@php
    /** @var string $body */
    /** @var string $emailSubject */
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $emailSubject }}</title>
</head>
<body style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #1c1410; background-color: #fef9ef; margin: 0; padding: 20px;">
    <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">

        {{-- Header --}}
        <div style="background: linear-gradient(135deg, #1c1410 0%, #2a1f18 100%); text-align: center; padding: 36px 20px;">
            <h1 style="margin: 0; font-size: 30px; font-weight: 700; color: #d4920c; letter-spacing: -0.5px;">KneadIt</h1>
            <p style="margin: 6px 0 0; color: #d4a574; font-size: 13px;">Your bakery management platform</p>
        </div>

        {{-- Content --}}
        <div style="padding: 32px 40px;">
            <div style="color: #1c1410; font-size: 15px;">{!! clean($body) !!}</div>
        </div>

        {{-- Footer --}}
        <div style="background-color: #fef9ef; padding: 20px 40px; border-top: 1px solid #e8d0b0; text-align: center;">
            <p style="margin: 0; color: #6b4c3b; font-size: 12px;">
                You're receiving this as the owner of a KneadIt bakery.
            </p>
        </div>
    </div>
</body>
</html>
