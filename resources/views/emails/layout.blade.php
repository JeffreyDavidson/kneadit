<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'KneadIt Bakery')</title>
</head>
<body style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #1c1410; background-color: #fef9ef; margin: 0; padding: 20px;">
    <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
        {{-- Header --}}
        <div style="text-align: center; padding: 30px 20px 15px;">
            <h1 style="margin: 0; font-size: 28px; font-weight: 700; color: #1c1410;">{{ \App\Models\Setting::get('store_name', 'KneadIt Bakery') }}</h1>
        </div>
        <div style="height: 3px; background-color: #d4920c; margin: 0 40px 10px;"></div>

        {{-- Content --}}
        <div style="padding: 20px 40px 30px;">
            @yield('content')
        </div>

        {{-- Footer --}}
        <div style="background-color: #1c1410; color: #fef9ef; text-align: center; padding: 25px 20px; font-size: 14px;">
            <p style="margin: 0 0 5px; font-weight: 600;">{{ \App\Models\Setting::get('store_name', 'KneadIt Bakery') }}</p>
            <p style="margin: 0 0 5px; opacity: 0.85;">{{ \App\Models\Setting::get('store_address', '') }}</p>
            @if(\App\Models\Setting::get('store_phone'))
                <p style="margin: 0 0 5px; opacity: 0.85;">📞 {{ \App\Models\Setting::get('store_phone') }}</p>
            @endif
            <p style="margin: 15px 0 0; opacity: 0.6; font-size: 12px;">Powered by KneadIt</p>
        </div>
    </div>
</body>
</html>
