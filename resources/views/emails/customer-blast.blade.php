<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $storeName }}</title>
</head>
<body style="margin: 0; padding: 0; background-color: #fef9ef; font-family: Georgia, 'Times New Roman', serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color: #fef9ef;">
        <tr>
            <td align="center" style="padding: 40px 20px;">
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(28, 20, 16, 0.1);">
                    {{-- Header --}}
                    <tr>
                        <td style="background-color: #1c1410; padding: 30px 40px; text-align: center;">
                            <h1 style="margin: 0; color: #d4920c; font-size: 28px; font-weight: bold; letter-spacing: 1px;">
                                {{ $storeName }}
                            </h1>
                        </td>
                    </tr>

                    {{-- Body --}}
                    <tr>
                        <td style="padding: 40px; color: #1c1410; font-size: 16px; line-height: 1.6;">
                            {!! $body !!}
                        </td>
                    </tr>

                    {{-- Footer --}}
                    <tr>
                        <td style="background-color: #1c1410; padding: 20px 40px; text-align: center;">
                            <p style="margin: 0; color: #d4920c; font-size: 12px;">
                                {{ $storeName }}
                            </p>
                            <p style="margin: 8px 0 0; color: #a89585; font-size: 11px;">
                                Reply STOP to unsubscribe
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
