@php
    $logoPath = is_array($page->branding['store_logo'] ?? null) ? collect($page->branding['store_logo'])->first() : ($page->branding['store_logo'] ?? null);
    $primary = $page->branding['color_primary'] ?: '#d4920c';
    $secondary = $page->branding['color_secondary'] ?: '#8b6844';
    $days = ['monday' => 'Mon', 'tuesday' => 'Tue', 'wednesday' => 'Wed', 'thursday' => 'Thu', 'friday' => 'Fri', 'saturday' => 'Sat', 'sunday' => 'Sun'];
    $methods = is_array($page->payments['payment_methods'] ?? null) ? $page->payments['payment_methods'] : ['cash'];
@endphp

<div style="max-width: 720px; margin: 0 auto; font-family: system-ui, -apple-system, sans-serif;">

    {{-- Storefront Preview Card --}}
    <div style="background: linear-gradient(135deg, #1c1410 0%, #2a1f18 100%); border-radius: 16px; overflow: hidden; box-shadow: 0 20px 60px rgba(0,0,0,0.3);">

        {{-- Hero Banner --}}
        <div style="background: linear-gradient(135deg, {{ $primary }}22 0%, {{ $secondary }}22 100%); padding: 40px 32px; text-align: center; border-bottom: 1px solid rgba(255,255,255,0.06);">
            @if ($logoPath)
                <img src="{{ Storage::url($logoPath) }}" alt="Logo" style="max-height: 64px; margin-bottom: 16px; border-radius: 8px;">
            @endif
            <h2 style="color: #fef9ef; margin: 0; font-size: 28px; font-weight: 700; letter-spacing: -0.5px;">{{ $page->welcome['bakery_name'] ?: 'Your Bakery' }}</h2>
            <p style="color: {{ $primary }}; margin: 6px 0 0; font-size: 14px; font-weight: 600; text-transform: uppercase; letter-spacing: 1px;">{{ $page->welcome['owner_name'] }}</p>

            {{-- Brand Colors --}}
            <div style="display: flex; gap: 8px; justify-content: center; margin-top: 20px;">
                <div style="width: 32px; height: 32px; border-radius: 50%; background: {{ $primary }}; border: 2px solid rgba(255,255,255,0.15);"></div>
                <div style="width: 32px; height: 32px; border-radius: 50%; background: {{ $secondary }}; border: 2px solid rgba(255,255,255,0.15);"></div>
            </div>
        </div>

        {{-- Content Grid --}}
        <div style="padding: 28px 32px; display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">

            {{-- Contact --}}
            <div style="background: rgba(255,255,255,0.04); border-radius: 12px; padding: 20px; border: 1px solid rgba(255,255,255,0.06);">
                <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 12px;">
                    <x-heroicon-o-envelope class="w-4 h-4" style="color: {{ $primary }};" stroke-width="2" />
                    <span style="color: #fef9ef; font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Contact</span>
                </div>
                <div style="color: #d4a574; font-size: 13px; line-height: 1.8;">
                    @if ($page->contact['email'])<div>{{ $page->contact['email'] }}</div>@endif
                    @if ($page->contact['phone'])<div>{{ $page->contact['phone'] }}</div>@endif
                    @if ($page->contact['address'])<div style="margin-top: 4px;">{{ $page->contact['address'] }}</div>@endif
                    @if (!$page->contact['email'] && !$page->contact['phone'])
                        <div style="color: #6b4c3b; font-style: italic;">Not set yet</div>
                    @endif
                </div>
            </div>

            {{-- Fulfillment --}}
            <div style="background: rgba(255,255,255,0.04); border-radius: 12px; padding: 20px; border: 1px solid rgba(255,255,255,0.06);">
                <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 12px;">
                    <x-heroicon-o-truck class="w-4 h-4" style="color: {{ $primary }};" stroke-width="2" />
                    <span style="color: #fef9ef; font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Fulfillment</span>
                </div>
                <div style="display: flex; flex-direction: column; gap: 6px;">
                    <span style="display: inline-flex; align-items: center; gap: 6px; font-size: 13px; color: {{ $page->delivery['delivery_enabled'] ? '#6ee7b7' : '#6b4c3b' }};">
                        <span style="font-size: 11px;">{{ $page->delivery['delivery_enabled'] ? '●' : '○' }}</span>
                        Delivery{{ $page->delivery['delivery_enabled'] && $page->delivery['delivery_fee'] ? ' — ' : '' }}@if ($page->delivery['delivery_enabled'] && $page->delivery['delivery_fee'])@money((float)$page->delivery['delivery_fee'])@endif
                    </span>
                    <span style="display: inline-flex; align-items: center; gap: 6px; font-size: 13px; color: {{ $page->delivery['pickup_enabled'] ? '#6ee7b7' : '#6b4c3b' }};">
                        <span style="font-size: 11px;">{{ $page->delivery['pickup_enabled'] ? '●' : '○' }}</span>
                        Pickup
                    </span>
                </div>
            </div>

            {{-- Business Hours --}}
            <div style="background: rgba(255,255,255,0.04); border-radius: 12px; padding: 20px; border: 1px solid rgba(255,255,255,0.06);">
                <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 12px;">
                    <x-heroicon-o-clock class="w-4 h-4" style="color: {{ $primary }};" stroke-width="2" />
                    <span style="color: #fef9ef; font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Hours</span>
                </div>
                <div style="font-size: 12px; line-height: 1.9;">
                    @foreach ($days as $key => $label)
                        <div style="display: flex; justify-content: space-between;">
                            <span style="color: #d4a574; font-weight: 600;">{{ $label }}</span>
                            @if ($page->hours[$key] ?? false)
                                <span style="color: #fef9ef;">{{ $page->hours[$key . '_open'] }} – {{ $page->hours[$key . '_close'] }}</span>
                            @else
                                <span style="color: #6b4c3b;">Closed</span>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Payments --}}
            <div style="background: rgba(255,255,255,0.04); border-radius: 12px; padding: 20px; border: 1px solid rgba(255,255,255,0.06);">
                <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 12px;">
                    <x-heroicon-o-credit-card class="w-4 h-4" style="color: {{ $primary }};" stroke-width="2" />
                    <span style="color: #fef9ef; font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Payments</span>
                </div>
                <div style="display: flex; flex-direction: column; gap: 6px;">
                    @php
                        $methodLabels = ['stripe' => 'Stripe Connect', 'paypal' => 'PayPal', 'cash' => 'Cash / Manual'];
                    @endphp
                    @foreach ($methods as $method)
                        @if (isset($methodLabels[$method]))
                            <span style="display: inline-flex; align-items: center; gap: 6px; font-size: 13px; color: #6ee7b7;">
                                <span style="font-size: 11px;">●</span>
                                {{ $methodLabels[$method] }}
                                @if ($method === 'paypal' && ($page->payments['paypal_sandbox'] ?? false))
                                    <span style="font-size: 10px; color: {{ $primary }}; background: {{ $primary }}22; padding: 1px 6px; border-radius: 4px;">sandbox</span>
                                @endif
                            </span>
                        @endif
                    @endforeach
                    @if (empty($methods))
                        <span style="color: #6b4c3b; font-style: italic; font-size: 13px;">Not configured</span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Featured Product --}}
        @if ($page->product['name'] ?? '')
        <div style="padding: 0 32px 28px;">
            <div style="background: linear-gradient(135deg, {{ $primary }}15 0%, {{ $secondary }}10 100%); border: 1px solid {{ $primary }}33; border-radius: 12px; padding: 20px; display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <span style="color: {{ $primary }}; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 1.5px;">Featured Product</span>
                    <div style="color: #fef9ef; font-size: 16px; font-weight: 600; margin-top: 4px;">{{ $page->product['name'] }}</div>
                    @if ($page->product['description'] ?? '')
                        <div style="color: #d4a574; font-size: 12px; margin-top: 4px; max-width: 400px;">{{ Str::limit($page->product['description'], 80) }}</div>
                    @endif
                </div>
                <div style="color: #fef9ef; font-size: 24px; font-weight: 700;">@money((float)$page->product['price'])</div>
            </div>
        </div>
        @endif

        {{-- Subdomain --}}
        <div style="padding: 16px 32px; border-top: 1px solid rgba(255,255,255,0.06); text-align: center;">
            <span style="color: #6b4c3b; font-size: 12px;">Your storefront will be live at</span>
            <div style="color: {{ $primary }}; font-size: 14px; font-weight: 600; margin-top: 4px;">
                {{ $page->welcome['bakery_name'] ? Str::slug($page->welcome['bakery_name']) . '.getkneadit.app' : 'your-bakery.getkneadit.app' }}
            </div>
        </div>
    </div>

    {{-- Ready Message --}}
    <div style="text-align: center; margin-top: 24px; padding: 0 16px;">
        <p style="color: #d4a574; font-size: 14px; margin: 0;">
            Everything look good? Hit <strong style="color: #fef9ef;">Finish</strong> to launch your bakery.
            <br><span style="font-size: 12px; color: #6b4c3b;">You can change all of this later from your admin dashboard.</span>
        </p>
    </div>
</div>
