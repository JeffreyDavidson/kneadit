<div style="background: #fdf8f2; border-radius: 16px; padding: 32px; max-width: 640px; margin: 0 auto; font-family: system-ui, sans-serif;">
    {{-- Header --}}
    <div style="text-align: center; margin-bottom: 24px;">
        @php
            $logoPath = is_array($page->store_logo) ? collect($page->store_logo)->first() : $page->store_logo;
        @endphp
        @if($logoPath)
            <img src="{{ Storage::url($logoPath) }}" alt="Logo" style="max-height: 80px; margin-bottom: 12px; border-radius: 8px;">
        @endif
        <h2 style="color: #3d2314; margin: 0; font-size: 24px;">{{ $page->bakery_name ?: 'Your Bakery' }}</h2>
        <p style="color: #6b4c3b; margin: 4px 0 0;">{{ $page->owner_name }}</p>
    </div>

    {{-- Brand Colors --}}
    <div style="display: flex; gap: 12px; justify-content: center; margin-bottom: 24px;">
        <div style="display: flex; align-items: center; gap: 6px;">
            <span style="display: inline-block; width: 24px; height: 24px; border-radius: 50%; background: {{ $page->brand_color_primary }}; border: 2px solid #e8d0b0;"></span>
            <span style="color: #6b4c3b; font-size: 13px;">Primary</span>
        </div>
        <div style="display: flex; align-items: center; gap: 6px;">
            <span style="display: inline-block; width: 24px; height: 24px; border-radius: 50%; background: {{ $page->brand_color_secondary }}; border: 2px solid #e8d0b0;"></span>
            <span style="color: #6b4c3b; font-size: 13px;">Secondary</span>
        </div>
    </div>

    <hr style="border: none; border-top: 1px solid #e8d0b0; margin: 16px 0;">

    {{-- Contact Info --}}
    <div style="margin-bottom: 20px;">
        <h3 style="color: #3d2314; font-size: 16px; margin: 0 0 8px;">📧 Contact</h3>
        <div style="color: #6b4c3b; font-size: 14px; line-height: 1.6;">
            @if($page->contact_email)<div>{{ $page->contact_email }}</div>@endif
            @if($page->contact_phone)<div>{{ $page->contact_phone }}</div>@endif
            @if($page->contact_address)<div>{{ $page->contact_address }}</div>@endif
        </div>
    </div>

    {{-- Business Hours --}}
    <div style="margin-bottom: 20px;">
        <h3 style="color: #3d2314; font-size: 16px; margin: 0 0 8px;">🕐 Business Hours</h3>
        <div style="color: #6b4c3b; font-size: 14px; line-height: 1.8;">
            @php
                $days = ['monday' => 'Mon', 'tuesday' => 'Tue', 'wednesday' => 'Wed', 'thursday' => 'Thu', 'friday' => 'Fri', 'saturday' => 'Sat', 'sunday' => 'Sun'];
            @endphp
            @foreach($days as $key => $label)
                <div style="display: flex; justify-content: space-between; max-width: 300px;">
                    <span style="font-weight: 600;">{{ $label }}</span>
                    @if($page->{"hours_{$key}"})
                        <span>{{ $page->{"hours_{$key}_open"} }} – {{ $page->{"hours_{$key}_close"} }}</span>
                    @else
                        <span style="color: #d4a574;">Closed</span>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    {{-- First Product --}}
    @if($page->product_name)
    <div style="margin-bottom: 20px;">
        <h3 style="color: #3d2314; font-size: 16px; margin: 0 0 8px;">🧁 Featured Product</h3>
        <div style="background: white; border: 1px solid #e8d0b0; border-radius: 10px; padding: 16px;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <span style="color: #3d2314; font-weight: 600;">{{ $page->product_name }}</span>
                <span style="color: #6b4c3b; font-weight: 700;">${{ number_format((float)$page->product_price, 2) }}</span>
            </div>
            @if($page->product_description)
                <p style="color: #6b4c3b; font-size: 13px; margin: 8px 0 0;">{{ $page->product_description }}</p>
            @endif
        </div>
    </div>
    @endif

    {{-- Delivery / Pickup --}}
    <div style="margin-bottom: 20px;">
        <h3 style="color: #3d2314; font-size: 16px; margin: 0 0 8px;">🚚 Order Fulfillment</h3>
        <div style="display: flex; gap: 12px; flex-wrap: wrap;">
            <span style="display: inline-block; padding: 6px 14px; border-radius: 20px; font-size: 13px; font-weight: 600;
                background: {{ $page->delivery_enabled ? '#d4f5d4' : '#f5e0d0' }};
                color: {{ $page->delivery_enabled ? '#1a5c1a' : '#6b4c3b' }};">
                {{ $page->delivery_enabled ? '✓ Delivery' : '✗ No Delivery' }}
            </span>
            <span style="display: inline-block; padding: 6px 14px; border-radius: 20px; font-size: 13px; font-weight: 600;
                background: {{ $page->pickup_enabled ? '#d4f5d4' : '#f5e0d0' }};
                color: {{ $page->pickup_enabled ? '#1a5c1a' : '#6b4c3b' }};">
                {{ $page->pickup_enabled ? '✓ Pickup' : '✗ No Pickup' }}
            </span>
        </div>
    </div>

    {{-- PayPal --}}
    <div style="margin-bottom: 8px;">
        <h3 style="color: #3d2314; font-size: 16px; margin: 0 0 8px;">💳 Payments</h3>
        @if($page->paypal_client_id)
            <span style="display: inline-block; padding: 6px 14px; border-radius: 20px; font-size: 13px; font-weight: 600; background: #d4f5d4; color: #1a5c1a;">
                ✓ PayPal Connected{{ $page->paypal_sandbox ? ' (Sandbox)' : '' }}
            </span>
        @else
            <span style="display: inline-block; padding: 6px 14px; border-radius: 20px; font-size: 13px; font-weight: 600; background: #f5e0d0; color: #6b4c3b;">
                PayPal not connected — you can set this up later
            </span>
        @endif
    </div>
</div>
