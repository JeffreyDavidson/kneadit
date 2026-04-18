@php
/** @var string $senderName */
/** @var string $senderEmail */
/** @var string $body */
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>KneadIt Contact Form</title>
</head>
<body style="font-family: Arial, sans-serif; color: #333;">
    <h2 style="margin: 0 0 16px;">New contact form submission</h2>
    <p><strong>From:</strong> {{ $senderName }} &lt;{{ $senderEmail }}&gt;</p>
    <hr style="border: none; border-top: 1px solid #eee; margin: 20px 0;">
    <p style="white-space: pre-wrap;">{{ $body }}</p>
</body>
</html>
