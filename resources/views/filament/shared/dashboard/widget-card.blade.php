@props(['widget'])

@php
    $columns = (\App\Enums\Filament\WidgetSize::tryFrom($widget['size'] ?? '') ?? \App\Enums\Filament\WidgetSize::Small)->columns();
@endphp

<div class="preview-widget" style="grid-column: span {{ $columns }};">
    <div class="preview-widget-header">
        <span class="pw-icon">{{ $widget['icon'] }}</span>
        <span>{{ $widget['name'] }}</span>
    </div>
    <div class="preview-widget-body">
        @switch($widget['key'])
            @case('welcome_banner')
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <div style="font-size: 0.85rem; font-weight: 600; color: #3d2314;">Good evening!</div>
                        <div style="font-size: 0.65rem; color: #a08060;">Saturday, March 14</div>
                    </div>
                    <div style="display: flex; gap: 6px;">
                        <div style="background: #fdf8f2; border-radius: 6px; padding: 4px 8px; text-align: center;">
                            <div style="font-size: 0.9rem; font-weight: 700; color: #3d2314;">3</div>
                            <div style="font-size: 0.55rem; color: #a08060;">ORDERS</div>
                        </div>
                        <div style="background: #fdf8f2; border-radius: 6px; padding: 4px 8px; text-align: center;">
                            <div style="font-size: 0.9rem; font-weight: 700; color: #3d2314;">$85</div>
                            <div style="font-size: 0.55rem; color: #a08060;">REVENUE</div>
                        </div>
                    </div>
                </div>
                @break
            @case('stats_overview')
                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 6px;">
                    @foreach (['Orders: 5', 'Pending: 2', 'Revenue: $142'] as $stat)
                        <div style="background: #fdf8f2; border-radius: 6px; padding: 6px 8px;">
                            <div style="font-size: 0.6rem; color: #a08060;">{{ explode(':', $stat)[0] }}</div>
                            <div style="font-size: 0.85rem; font-weight: 700; color: #3d2314;">{{ trim(explode(':', $stat)[1]) }}</div>
                        </div>
                    @endforeach
                </div>
                @break
            @case('revenue_chart')
                <div class="pw-line">
                    @foreach ([30, 45, 20, 60, 80, 55, 70, 40, 90, 65, 50, 75] as $h)
                        <div class="pw-line-bar" style="height: {{ $h }}%;"></div>
                    @endforeach
                </div>
                @break
            @case('recent_orders')
            @case('upcoming_orders')
                @for ($i = 0; $i < 3; $i++)
                    <div class="pw-row">
                        <span><span class="pw-dot" style="background: {{ ['#d4a574','#e8b04a','#8b6844'][$i] }};"></span>Order #{{ 100 + $i }}</span>
                        <span>${{ [28, 45, 32][$i] }}</span>
                    </div>
                @endfor
                @break
            @case('top_products')
                @foreach (['Chocolate Cake' => 85, 'Banana Bread' => 60, 'Cookies' => 40] as $name => $pct)
                    <div style="margin-bottom: 6px;">
                        <div style="display: flex; justify-content: space-between; font-size: 0.65rem; color: #6b4c3b;">
                            <span>{{ $name }}</span><span>{{ $pct }}%</span>
                        </div>
                        <div class="pw-bar"><div class="pw-bar-fill" style="width: {{ $pct }}%;"></div></div>
                    </div>
                @endforeach
                @break
            @case('customer_insights')
                <div class="pw-stat"><span class="pw-stat-label">New this week</span><span class="pw-stat-value">4</span></div>
                <div class="pw-stat" style="margin-top: 8px;"><span class="pw-stat-label">Repeat rate</span><span class="pw-stat-value">62%</span></div>
                @break
            @case('weekly_revenue')
                <div class="pw-line">
                    @foreach ([50, 70, 45, 80, 65, 90, 55] as $h)
                        <div class="pw-line-bar" style="height: {{ $h }}%;"></div>
                    @endforeach
                </div>
                <div style="display: flex; justify-content: space-between; font-size: 0.6rem; color: #a08060; margin-top: 4px;">
                    <span>Mon</span><span>Thu</span><span>Sun</span>
                </div>
                @break
            @case('order_funnel')
                @foreach (['Pending' => '#e8b04a', 'Confirmed' => '#d4a574', 'Delivered' => '#8b6844'] as $status => $color)
                    <div class="pw-row">
                        <span><span class="pw-dot" style="background: {{ $color }};"></span>{{ $status }}</span>
                        <span>{{ ['Pending' => 3, 'Confirmed' => 5, 'Delivered' => 12][$status] }}</span>
                    </div>
                @endforeach
                @break
            @case('todays_orders')
                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 6px;">
                    @foreach (['9:00 AM' => '$28', '11:30 AM' => '$45', '2:00 PM' => '$32'] as $time => $amt)
                        <div style="background: #fdf8f2; border-radius: 6px; padding: 6px 8px;">
                            <div style="font-size: 0.6rem; color: #a08060;">{{ $time }}</div>
                            <div style="font-size: 0.8rem; font-weight: 700; color: #3d2314;">{{ $amt }}</div>
                        </div>
                    @endforeach
                </div>
                @break
            @case('baking_sheet')
                @foreach (['Chocolate Cake ×2', 'Banana Bread ×4', 'Sugar Cookies ×24'] as $item)
                    <div class="pw-row">
                        <span>{{ $item }}</span>
                        <span style="color: #d4a574;">○</span>
                    </div>
                @endforeach
                @break
            @case('inbox')
                <div class="pw-stat"><span class="pw-stat-label">Unread</span><span class="pw-stat-value">3</span></div>
                <div style="font-size: 0.65rem; color: #a08060; margin-top: 4px;">Latest: "Can I add to my order?"</div>
                @break
            @case('margin_alert')
                <div class="pw-row"><span>🔴 Cookies</span><span>12%</span></div>
                <div class="pw-row"><span>🟡 Brownies</span><span>28%</span></div>
                @break
            @case('goal_tracker')
                <div style="margin-bottom: 6px;">
                    <div style="display: flex; justify-content: space-between; font-size: 0.65rem; color: #6b4c3b;">
                        <span>Monthly Revenue</span><span>$850 / $1,200</span>
                    </div>
                    <div class="pw-bar"><div class="pw-bar-fill" style="width: 71%;"></div></div>
                </div>
                <div>
                    <div style="display: flex; justify-content: space-between; font-size: 0.65rem; color: #6b4c3b;">
                        <span>New Customers</span><span>8 / 15</span>
                    </div>
                    <div class="pw-bar"><div class="pw-bar-fill" style="width: 53%;"></div></div>
                </div>
                @break
            @case('upcoming_holiday')
                <div style="text-align: center;">
                    <div style="font-size: 1.2rem;">🐣</div>
                    <div style="font-size: 0.75rem; font-weight: 600; color: #3d2314;">Easter</div>
                    <div style="font-size: 0.6rem; color: #a08060;">in 12 days</div>
                </div>
                @break
            @case('storefront_views')
                <div class="pw-stat"><span class="pw-stat-label">Today</span><span class="pw-stat-value">47</span></div>
                <div style="font-size: 0.6rem; color: #6b9e3a; margin-top: 2px;">↑ 12% vs yesterday</div>
                @break
            @case('coupon_usage')
                <div class="pw-stat"><span class="pw-stat-label">Active</span><span class="pw-stat-value">4</span></div>
                <div class="pw-row" style="margin-top: 6px;"><span>WELCOME10</span><span>23 uses</span></div>
                <div class="pw-row"><span>SPRING20</span><span>8 uses</span></div>
                @break
            @case('gift_card_balance')
                <div class="pw-stat"><span class="pw-stat-label">Outstanding</span><span class="pw-stat-value">$340</span></div>
                <div style="font-size: 0.65rem; color: #a08060; margin-top: 4px;">12 active cards</div>
                @break
            @case('loyalty_leaders')
                @foreach (['Sarah M.' => '520 pts', 'Mike R.' => '380 pts', 'Lisa K.' => '290 pts'] as $name => $pts)
                    <div class="pw-row"><span>{{ $name }}</span><span>{{ $pts }}</span></div>
                @endforeach
                @break
            @case('capacity_today')
                <div style="margin-bottom: 8px;">
                    <div style="display: flex; justify-content: space-between; font-size: 0.65rem; color: #6b4c3b;"><span>Today</span><span>72%</span></div>
                    <div class="pw-bar"><div class="pw-bar-fill" style="width: 72%;"></div></div>
                </div>
                <div>
                    <div style="display: flex; justify-content: space-between; font-size: 0.65rem; color: #6b4c3b;"><span>Tomorrow</span><span>35%</span></div>
                    <div class="pw-bar"><div class="pw-bar-fill" style="width: 35%;"></div></div>
                </div>
                @break
            @case('catering_pipeline')
                <div class="pw-stat"><span class="pw-stat-label">Open</span><span class="pw-stat-value">3</span></div>
                <div style="font-size: 0.6rem; color: #a08060; margin-top: 4px;">Pipeline: $1,250</div>
                @break
            @case('seasonal_items')
                <div class="pw-row"><span style="color: #6b9e3a;">● Coming Soon</span><span>2</span></div>
                <div class="pw-row"><span style="color: #d4574a;">● Ending Soon</span><span>1</span></div>
                <div class="pw-row"><span style="color: #d4a574;">● In Season</span><span>5</span></div>
                @break
            @case('review_summary')
                <div style="display: flex; align-items: baseline; gap: 6px;">
                    <span style="font-size: 1.2rem; font-weight: 700; color: #3d2314;">4.8</span>
                    <span style="font-size: 0.75rem; color: #e8b04a;">★★★★★</span>
                </div>
                <div style="font-size: 0.6rem; color: #a08060; margin-top: 2px;">28 reviews</div>
                @break
            @case('reorder_reminders')
                <div class="pw-stat"><span class="pw-stat-label">Lapsed</span><span class="pw-stat-value">6</span></div>
                <div style="font-size: 0.6rem; color: #a08060; margin-top: 4px;">Haven't ordered in 30+ days</div>
                @break
            @default
                <div style="display: flex; flex-direction: column; gap: 4px;">
                    @for ($i = 0; $i < 2; $i++)
                        <div style="height: 8px; border-radius: 4px; background: rgba(212,165,116,0.12); width: {{ [100, 70][$i] }}%;"></div>
                    @endfor
                </div>
        @endswitch
    </div>
</div>
