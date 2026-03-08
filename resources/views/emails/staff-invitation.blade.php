<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>You're Invited!</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f8f5f0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">
    <div style="max-width: 600px; margin: 40px auto; background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.07);">
        <div style="background: linear-gradient(135deg, #8B6914, #D4A843); padding: 32px; text-align: center;">
            <h1 style="color: white; margin: 0; font-size: 24px;">🍞 KneadIt</h1>
        </div>
        <div style="padding: 32px;">
            <h2 style="color: #333; margin-top: 0;">You've been invited!</h2>
            <p style="color: #555; font-size: 16px; line-height: 1.6;">
                You've been invited to join <strong>{{ $storeName }}</strong> on KneadIt as a <strong>{{ $role }}</strong>.
            </p>
            <div style="text-align: center; margin: 32px 0;">
                <a href="{{ $acceptUrl }}" style="display: inline-block; background: #8B6914; color: white; text-decoration: none; padding: 14px 32px; border-radius: 8px; font-size: 16px; font-weight: 600;">
                    Accept Invitation
                </a>
            </div>
            <p style="color: #888; font-size: 14px;">
                This invitation expires on {{ $expiresAt }}.
            </p>
        </div>
        <div style="background: #f8f5f0; padding: 16px 32px; text-align: center;">
            <p style="color: #999; font-size: 12px; margin: 0;">Powered by KneadIt — The bakery management platform</p>
        </div>
    </div>
</body>
</html>
