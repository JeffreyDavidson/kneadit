{{--
    The shared layout for every email KneadIt sends as itself (not as a bakery).
    It carries KneadIt's own branding and never reads bakery settings, so it
    renders in the queue worker. Each email fills `title` and `content`.
--}}
@php
    $kneaditUrl = config('app.url');
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'KneadIt')</title>
</head>
<body style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #1c1410; background-color: #fef9ef; margin: 0; padding: 20px;">
    <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">

        {{-- Header --}}
        <div style="background: linear-gradient(135deg, #1c1410 0%, #2a1f18 100%); text-align: center; padding: 36px 20px;">
            <a href="{{ $kneaditUrl }}" style="text-decoration: none;">
                <img src="{{ $kneaditUrl }}/images/logo-transparent.png" alt="KneadIt" width="200" style="display: inline-block; width: 200px; max-width: 70%; height: auto; border: 0;">
            </a>
            <p style="margin: 12px 0 0; color: #d4a574; font-size: 14px;">Your bakery management platform</p>
        </div>

        {{-- Content --}}
        <div style="padding: 32px 40px; color: #4a3728;">
            @yield('content')
        </div>

        {{-- Footer --}}
        <div style="background: #1c1410; color: #fef9ef; text-align: center; padding: 24px 20px; font-size: 13px;">
            <p style="margin: 0 0 4px; font-weight: 600;"><a href="{{ $kneaditUrl }}" style="color: #d4920c; text-decoration: none;">KneadIt</a></p>
            <p style="margin: 0; opacity: 0.6;">The bakery management platform for cottage food bakers</p>
        </div>
    </div>
</body>
</html>
