@props(['widget', 'configMode' => false, 'index' => null])

@php
    $columns = (\App\Enums\Filament\WidgetSize::tryFrom($widget['size'] ?? '') ?? \App\Enums\Filament\WidgetSize::Small)->columns();
    $isHidden = $configMode && ! ($widget['visible'] ?? true);
@endphp

<div
    @class([
        'preview-widget',
        'config-tile' => $configMode,
        'is-hidden' => $isHidden,
    ])
    style="grid-column: span {{ $columns }};"
    @if ($configMode) data-index="{{ $index }}" @endif
>
    @if ($configMode)
        @php
            $allowedSizes = \App\Filament\Shared\Dashboard\WidgetMeta::allowedSizesFor($widget['key']);
        @endphp

        <div class="config-controls">
            <button type="button" class="config-ctrl config-drag" title="Drag to reorder">
                <x-heroicon-s-bars-3 class="w-4 h-4" />
            </button>

            @if (count($allowedSizes) > 1)
                <div class="config-size-group">
                    @foreach ($allowedSizes as $size)
                        <button
                            type="button"
                            class="config-size-btn {{ ($widget['size'] ?? 'sm') === $size->value ? 'active' : '' }}"
                            wire:click="setSize({{ $index }}, '{{ $size->value }}')"
                            title="{{ $size->label() }} ({{ $size->columns() }}/3 width)"
                        >{{ strtoupper($size->value) }}</button>
                    @endforeach
                </div>
            @else
                <span class="config-size-locked" title="This widget is fixed at {{ $allowedSizes[0]->label() }}">{{ strtoupper($allowedSizes[0]->value) }}</span>
            @endif

            <button
                type="button"
                class="config-ctrl config-toggle {{ ($widget['visible'] ?? true) ? 'is-on' : 'is-off' }}"
                wire:click="toggleWidget({{ $index }})"
                title="{{ ($widget['visible'] ?? true) ? 'Hide widget' : 'Show widget' }}"
            >
                @if ($widget['visible'] ?? true)
                    <x-heroicon-s-eye class="w-4 h-4" />
                @else
                    <x-heroicon-s-eye-slash class="w-4 h-4" />
                @endif
            </button>
        </div>
    @endif

    <div class="preview-widget-header">
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
                <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 6px;">
                    @foreach ([
                        ['label' => "Today's Orders", 'value' => '5', 'delta' => '+12%'],
                        ['label' => 'Pending', 'value' => '2', 'delta' => 'Manageable'],
                        ['label' => "Week's Revenue", 'value' => '$142', 'delta' => '+8%'],
                        ['label' => 'Views Today', 'value' => '47', 'delta' => '+3%'],
                    ] as $stat)
                        <div style="background: #fdf8f2; border-radius: 6px; padding: 6px 8px;">
                            <div style="font-size: 0.55rem; color: #a08060; text-transform: uppercase; letter-spacing: 0.05em;">{{ $stat['label'] }}</div>
                            <div style="font-size: 0.95rem; font-weight: 700; color: #3d2314; line-height: 1.1;">{{ $stat['value'] }}</div>
                            <div style="font-size: 0.55rem; color: #6b9e3a; margin-top: 2px;">{{ $stat['delta'] }}</div>
                            <div class="pw-line" style="height: 16px; margin-top: 4px;">
                                @foreach ([20, 35, 25, 50, 40, 60, 55] as $h)
                                    <div class="pw-line-bar" style="height: {{ $h }}%;"></div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
                @break
            @case('revenue_chart')
                @php
                    // md = 30-day window, lg = 90-day window (matches RevenueChartWidget::windowDays).
                    $bars = ($widget['size'] ?? 'md') === 'lg'
                        ? [30, 45, 20, 35, 60, 50, 80, 55, 40, 70, 45, 65, 90, 75, 50, 85, 60, 95, 70, 55, 80, 65, 75, 50, 85, 70, 95, 80, 65, 90]
                        : [30, 45, 20, 60, 80, 55, 70, 40, 90, 65, 50, 75];
                    $label = ($widget['size'] ?? 'md') === 'lg' ? 'Last 90 Days · $4,820 ↑ 18%' : 'Last 30 Days · $1,540 ↑ 8%';
                @endphp
                <div style="font-size: 0.6rem; color: var(--accent); font-weight: 600; margin-bottom: 4px;">{{ $label }}</div>
                <div class="pw-line">
                    @foreach ($bars as $h)
                        <div class="pw-line-bar" style="height: {{ $h }}%;"></div>
                    @endforeach
                </div>
                <div style="display: flex; justify-content: space-between; font-size: 0.6rem; color: #a08060; margin-top: 4px;">
                    @if (($widget['size'] ?? 'md') === 'lg')
                        <span>Jan</span><span>Feb</span><span>Mar</span>
                    @else
                        <span>Wk 1</span><span>Wk 2</span><span>Wk 3</span><span>Wk 4</span>
                    @endif
                </div>
                @break
            @case('recent_orders')
            @case('upcoming_orders')
                @php
                    $rowCount = match ($widget['size'] ?? 'sm') {
                        'lg', 'md' => 5,
                        default => 3,
                    };
                    $colors = ['#d4a574', '#e8b04a', '#8b6844', '#6b9e3a', '#d4574a'];
                    $amounts = [28, 45, 32, 56, 19];
                @endphp
                @for ($i = 0; $i < $rowCount; $i++)
                    <div class="pw-row">
                        <span><span class="pw-dot" style="background: {{ $colors[$i] }};"></span>Order #{{ 100 + $i }}</span>
                        <span>${{ $amounts[$i] }}</span>
                    </div>
                @endfor
                @if (($widget['size'] ?? 'sm') === 'lg')
                    <div style="margin-top: 8px; padding-top: 6px; border-top: 1px solid var(--border-subtle); display: flex; justify-content: space-between; font-size: 0.65rem; color: #a08060;">
                        <span>Total</span><span style="color: var(--accent); font-weight: 600;">${{ array_sum($amounts) }}</span>
                    </div>
                @endif
                @break
            @case('top_products')
                @php
                    $products = ($widget['size'] ?? 'sm') === 'md'
                        ? ['Chocolate Cake' => 85, 'Banana Bread' => 60, 'Cookies' => 40, 'Sourdough' => 30, 'Croissants' => 22]
                        : ['Chocolate Cake' => 85, 'Banana Bread' => 60, 'Cookies' => 40];
                @endphp
                @foreach ($products as $name => $pct)
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
                @php
                    // lg adds last-week comparison overlay (matches WeeklyRevenueChart).
                    $thisWeek = [50, 70, 45, 80, 65, 90, 55];
                    $lastWeek = [40, 55, 60, 65, 50, 75, 60];
                    $isLarge = ($widget['size'] ?? 'md') === 'lg';
                @endphp
                @if ($isLarge)
                    <div style="display: flex; gap: 10px; font-size: 0.6rem; color: #a08060; margin-bottom: 4px;">
                        <span><span class="pw-dot" style="background: var(--accent);"></span>This week</span>
                        <span><span class="pw-dot" style="background: var(--border-medium);"></span>Last week</span>
                    </div>
                @endif
                <div class="pw-line">
                    @foreach ($thisWeek as $i => $h)
                        @if ($isLarge)
                            <div style="flex: 1; display: flex; gap: 1px; align-items: end;">
                                <div class="pw-line-bar" style="height: {{ $h }}%; background: var(--accent);"></div>
                                <div class="pw-line-bar" style="height: {{ $lastWeek[$i] }}%;"></div>
                            </div>
                        @else
                            <div class="pw-line-bar" style="height: {{ $h }}%;"></div>
                        @endif
                    @endforeach
                </div>
                <div style="display: flex; justify-content: space-between; font-size: 0.6rem; color: #a08060; margin-top: 4px;">
                    <span>Mon</span><span>Thu</span><span>Sun</span>
                </div>
                @break
            @case('order_funnel')
                @php
                    $funnel = ($widget['size'] ?? 'sm') === 'md'
                        ? ['Pending' => ['#e8b04a', 3], 'Confirmed' => ['#d4a574', 5], 'In Prep' => ['#6b9e3a', 4], 'Out for Delivery' => ['#3a8bd4', 2], 'Delivered' => ['#8b6844', 12]]
                        : ['Pending' => ['#e8b04a', 3], 'Confirmed' => ['#d4a574', 5], 'Delivered' => ['#8b6844', 12]];
                @endphp
                @if (($widget['size'] ?? 'sm') === 'md')
                    <div class="pw-stat" style="margin-bottom: 6px;"><span class="pw-stat-label">Total Active</span><span class="pw-stat-value">26</span></div>
                @endif
                @foreach ($funnel as $status => [$color, $count])
                    <div class="pw-row">
                        <span><span class="pw-dot" style="background: {{ $color }};"></span>{{ $status }}</span>
                        <span>{{ $count }}</span>
                    </div>
                @endforeach
                @break
            @case('todays_orders')
                @php
                    $slots = ($widget['size'] ?? 'md') === 'lg'
                        ? ['9:00 AM' => '$28', '10:30 AM' => '$15', '11:30 AM' => '$45', '1:00 PM' => '$22', '2:00 PM' => '$32']
                        : ['9:00 AM' => '$28', '11:30 AM' => '$45', '2:00 PM' => '$32'];
                @endphp
                @if (($widget['size'] ?? 'md') === 'lg')
                    <div style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 8px; padding-bottom: 6px; border-bottom: 1px solid var(--border-subtle);">
                        <span style="font-size: 0.6rem; color: #a08060; text-transform: uppercase;">Revenue Today</span>
                        <span style="font-size: 1rem; font-weight: 700; color: var(--accent);">$142</span>
                    </div>
                @endif
                <div style="display: grid; grid-template-columns: repeat({{ count($slots) }}, 1fr); gap: 6px;">
                    @foreach ($slots as $time => $amt)
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
                @php
                    $items = ($widget['size'] ?? 'sm') === 'md'
                        ? ['🔴 Cookies' => '12%', '🔴 Sugar Cookies' => '15%', '🟡 Brownies' => '28%', '🟡 Banana Bread' => '31%']
                        : ['🔴 Cookies' => '12%', '🟡 Brownies' => '28%'];
                @endphp
                @if (($widget['size'] ?? 'sm') === 'md')
                    <div class="pw-stat" style="margin-bottom: 6px;"><span class="pw-stat-label">At-risk products</span><span class="pw-stat-value">4</span></div>
                @endif
                @foreach ($items as $name => $pct)
                    <div class="pw-row"><span>{{ $name }}</span><span>{{ $pct }}</span></div>
                @endforeach
                @break
            @case('goal_tracker')
                @php
                    $goals = ($widget['size'] ?? 'md') === 'lg'
                        ? [
                            ['label' => 'Daily Goal', 'detail' => '$140 / $200 · 70%', 'pct' => 70],
                            ['label' => 'Weekly Goal', 'detail' => '$820 / $1,200 · 68%', 'pct' => 68],
                            ['label' => 'Monthly Goal', 'detail' => '$2,450 / $5,000 · 49%', 'pct' => 49],
                            ['label' => 'Yearly Goal', 'detail' => '$32,000 / $50,000 · 64%', 'pct' => 64],
                        ]
                        : [
                            ['label' => 'Monthly Goal', 'detail' => '$2,450 / $5,000 · 49%', 'pct' => 49],
                            ['label' => 'Yearly Goal', 'detail' => '$32,000 / $50,000 · 64%', 'pct' => 64],
                        ];
                @endphp
                @foreach ($goals as $goal)
                    <div style="margin-bottom: 6px;">
                        <div style="display: flex; justify-content: space-between; font-size: 0.65rem; color: #6b4c3b;">
                            <span>{{ $goal['label'] }}</span><span>{{ $goal['detail'] }}</span>
                        </div>
                        <div class="pw-bar"><div class="pw-bar-fill" style="width: {{ $goal['pct'] }}%;"></div></div>
                    </div>
                @endforeach
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
                @php
                    $days = ($widget['size'] ?? 'sm') === 'md'
                        ? ['Mon' => 72, 'Tue' => 35, 'Wed' => 60, 'Thu' => 80, 'Fri' => 95, 'Sat' => 88, 'Sun' => 25]
                        : ['Today' => 72, 'Tomorrow' => 35];
                @endphp
                @foreach ($days as $label => $pct)
                    <div style="margin-bottom: 6px;">
                        <div style="display: flex; justify-content: space-between; font-size: 0.65rem; color: #6b4c3b;"><span>{{ $label }}</span><span>{{ $pct }}%</span></div>
                        <div class="pw-bar"><div class="pw-bar-fill" style="width: {{ $pct }}%;"></div></div>
                    </div>
                @endforeach
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
            @case('low_stock')
                @foreach (['Flour' => '2 lbs', 'Butter' => '0 lbs', 'Vanilla' => '1 oz'] as $item => $remaining)
                    <div class="pw-row"><span>{{ $item }}</span><span style="color: #d4574a;">{{ $remaining }}</span></div>
                @endforeach
                @break
            @case('at_risk_customers')
                @foreach (['Sarah M.' => '45d ago', 'Mike R.' => '38d ago', 'Lisa K.' => '32d ago'] as $name => $when)
                    <div class="pw-row"><span>{{ $name }}</span><span>{{ $when }}</span></div>
                @endforeach
                @break
            @case('birthday')
                @foreach (['Emma T.' => 'Tomorrow', 'James P.' => 'In 3 days', 'Olivia C.' => 'In 5 days'] as $name => $when)
                    <div class="pw-row"><span>🎂 {{ $name }}</span><span>{{ $when }}</span></div>
                @endforeach
                @break
            @case('recent_activity')
                @foreach (['2m ago' => 'New order #142', '15m ago' => 'Product updated', '1h ago' => 'Customer signed up'] as $when => $what)
                    <div class="pw-row"><span>{{ $what }}</span><span>{{ $when }}</span></div>
                @endforeach
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
