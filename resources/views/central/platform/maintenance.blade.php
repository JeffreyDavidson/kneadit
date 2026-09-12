<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Maintenance - KneadIt</title>
    <link rel="stylesheet" href="{{ asset('css/maintenance.css') }}" />
</head>
<body>
    <div class="container">
        <div class="logo"><span>🍞</span> KneadIt</div>
        <div class="divider"></div>
        <h1>We'll be back soon!</h1>
        <p class="message">
            {{ $message ?? 'We are currently performing scheduled maintenance. We\'ll be back shortly!' }}
        </p>
        @if (! empty($scheduled_end))
            <div class="scheduled-end">
                ⏰ Expected back: {{ \Carbon\Carbon::parse($scheduled_end)->format('M j, Y \a\t g:i A') }}
            </div>
        @endif
    </div>
</body>
</html>
