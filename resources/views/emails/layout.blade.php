<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', $storeName ?? config('app.name'))</title>
</head>
{{-- Variables provided by BaseMailable::buildViewData(): $storeName, $primaryColor, $secondaryColor, $storeEmail, $storePhone, $storeAddress, $logoUrl, $platformHomeUrl --}}
<body style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #1c1410; background-color: #f5f0e8; margin: 0; padding: 20px;">
    <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.08);">

        {{-- Header with baker's branding --}}
        <div style="background-color: {{ $secondaryColor }}; text-align: center; padding: 28px 20px;">
            @if ($logoUrl)
                <img src="{{ $logoUrl }}" alt="{{ $storeName }}" style="max-height: 60px; margin-bottom: 10px; border-radius: 6px;">
            @endif
            <h1 style="margin: 0; font-size: 26px; font-weight: 700; color: #ffffff;">{{ $storeName }}</h1>
        </div>

        {{-- Accent bar in baker's primary color --}}
        <div style="height: 4px; background-color: {{ $primaryColor }};"></div>

        {{-- Content --}}
        <div style="padding: 28px 40px 32px;">
            @yield('content')
        </div>

        {{-- Footer --}}
        <div style="background-color: {{ $secondaryColor }}; color: #ffffff; text-align: center; padding: 24px 20px; font-size: 13px;">
            <p style="margin: 0 0 4px; font-weight: 600; color: {{ $primaryColor }};">{{ $storeName }}</p>
            @if ($storeAddress)
                <p style="margin: 0 0 4px; opacity: 0.8;">{{ $storeAddress }}</p>
            @endif
            @if ($storePhone)
                <p style="margin: 0 0 4px; opacity: 0.8;">{{ $storePhone }}</p>
            @endif
            @if ($storeEmail)
                <p style="margin: 0 0 4px; opacity: 0.8;">{{ $storeEmail }}</p>
            @endif
            <p style="margin: 14px 0 0; opacity: 0.4; font-size: 11px;">Powered by <a href="{{ $platformHomeUrl }}" style="color: {{ $primaryColor }}; text-decoration: none;">KneadIt</a></p>
        </div>
    </div>
</body>
</html>
