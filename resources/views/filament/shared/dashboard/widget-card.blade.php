@props(['widget', 'configMode' => false, 'index' => null])

@php
    $sizeEnum = \App\Enums\Filament\WidgetSize::tryFrom($widget['size'] ?? '') ?? \App\Enums\Filament\WidgetSize::Small;
    $columns = $sizeEnum->columns();
    $rows = $sizeEnum->rows();
    $isHidden = $configMode && ! ($widget['visible'] ?? true);
    $isXl = $sizeEnum === \App\Enums\Filament\WidgetSize::ExtraLarge;
@endphp

<div
    @class([
        'preview-widget',
        'config-tile' => $configMode,
        'is-hidden' => $isHidden,
        'preview-widget-xl' => $isXl,
    ])
    style="grid-column: span {{ $columns }}; grid-row: span {{ $rows }};"
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
                <div style="display: flex; flex-wrap: wrap; gap: 6px;">
                    @foreach (['+ New Order', '📄 View Orders', '✉ Messages'] as $label)
                        <span style="font-size: 0.7rem; font-weight: 600; padding: 4px 10px; border-radius: 6px; background: var(--brand-800); color: var(--brand-100); border: 1px solid var(--border-subtle);">{{ $label }}</span>
                    @endforeach
                </div>
                @break
            @case('stats_overview')
                @php
                    $isStatsXl = ($widget['size'] ?? 'lg') === 'xl';
                    $sparklineHeight = $isStatsXl ? 36 : 16;
                @endphp
                <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 6px;">
                    @foreach ([
                        ['label' => "Today's Orders", 'value' => '5', 'delta' => '+12%', 'spark' => [20, 35, 25, 50, 40, 60, 55]],
                        ['label' => 'Pending', 'value' => '2', 'delta' => 'Manageable', 'spark' => [40, 30, 20, 25, 35, 30, 25]],
                        ['label' => "Week's Revenue", 'value' => '$142', 'delta' => '+8%', 'spark' => [30, 40, 50, 45, 60, 65, 70]],
                        ['label' => 'Views Today', 'value' => '47', 'delta' => '+3%', 'spark' => [50, 60, 55, 65, 70, 65, 75]],
                    ] as $stat)
                        <div style="background: #fdf8f2; border-radius: 6px; padding: 6px 8px;">
                            <div style="font-size: 0.55rem; color: #a08060; text-transform: uppercase; letter-spacing: 0.05em;">{{ $stat['label'] }}</div>
                            <div style="font-size: 0.95rem; font-weight: 700; color: #3d2314; line-height: 1.1;">{{ $stat['value'] }}</div>
                            <div style="font-size: 0.55rem; color: #6b9e3a; margin-top: 2px;">{{ $stat['delta'] }}</div>
                            <div class="pw-line" style="height: {{ $sparklineHeight }}px; margin-top: 4px;">
                                @foreach ($stat['spark'] as $h)
                                    <div class="pw-line-bar" style="height: {{ $h }}%;"></div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
                @if ($isStatsXl)
                    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 6px; margin-top: 12px; padding-top: 12px; border-top: 1px solid var(--border-subtle);">
                        <div><div style="font-size: 0.55rem; color: #a08060; text-transform: uppercase;">Avg vs 7-day</div><div style="font-size: 0.7rem; color: #3d2314; font-weight: 600;">↑ 12%</div></div>
                        <div><div style="font-size: 0.55rem; color: #a08060; text-transform: uppercase;">Hours saved</div><div style="font-size: 0.7rem; color: #3d2314; font-weight: 600;">2.4h</div></div>
                        <div><div style="font-size: 0.55rem; color: #a08060; text-transform: uppercase;">Conversion</div><div style="font-size: 0.7rem; color: #3d2314; font-weight: 600;">11%</div></div>
                        <div><div style="font-size: 0.55rem; color: #a08060; text-transform: uppercase;">New / Returning</div><div style="font-size: 0.7rem; color: #3d2314; font-weight: 600;">3 / 2</div></div>
                    </div>
                @endif
                @break
            @case('revenue_chart')
                @php
                    // md = 30 days, lg = 90 days, xl = 90 days + breakdown table (matches RevenueChartWidget::windowDays).
                    $size = $widget['size'] ?? 'md';
                    $bars = match ($size) {
                        'xl' => [30, 45, 20, 35, 60, 50, 80, 55, 40, 70, 45, 65, 90, 75, 50, 85, 60, 95, 70, 55, 80, 65, 75, 50, 85, 70, 95, 80, 65, 90, 55, 75, 60, 80, 65, 90, 70, 55, 80, 95],
                        'lg' => [30, 45, 20, 35, 60, 50, 80, 55, 40, 70, 45, 65, 90, 75, 50, 85, 60, 95, 70, 55, 80, 65, 75, 50, 85, 70, 95, 80, 65, 90],
                        default => [30, 45, 20, 60, 80, 55, 70, 40, 90, 65, 50, 75],
                    };
                    $label = match ($size) {
                        'xl' => 'Last 90 Days · $4,820 ↑ 18%',
                        'lg' => 'Last 90 Days · $4,820 ↑ 18%',
                        default => 'Last 30 Days · $1,540 ↑ 8%',
                    };
                    $axisLabels = match ($size) {
                        'xl' => ['Jan', 'Jan 15', 'Feb', 'Feb 15', 'Mar', 'Mar 15', 'Apr'],
                        'lg' => ['Jan', 'Jan 15', 'Feb', 'Feb 15', 'Mar', 'Mar 15'],
                        default => ['Wk 1', 'Wk 2', 'Wk 3', 'Wk 4'],
                    };
                @endphp
                <div style="font-size: 0.6rem; color: var(--accent); font-weight: 600; margin-bottom: 4px;">{{ $label }}</div>
                <div class="pw-line" @if ($size === 'xl') style="height: 80px;" @endif>
                    @foreach ($bars as $h)
                        <div class="pw-line-bar" style="height: {{ $h }}%;"></div>
                    @endforeach
                </div>
                <div style="display: flex; justify-content: space-between; font-size: 0.6rem; color: #a08060; margin-top: 4px;">
                    @foreach ($axisLabels as $label)
                        <span>{{ $label }}</span>
                    @endforeach
                </div>
                @if ($size === 'xl')
                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; margin-top: 12px; padding-top: 12px; border-top: 1px solid var(--border-subtle);">
                        <div><div style="font-size: 0.55rem; color: #a08060; text-transform: uppercase;">Top Day</div><div style="font-size: 0.85rem; font-weight: 700; color: #3d2314;">$480</div><div style="font-size: 0.55rem; color: #a08060;">Mar 12</div></div>
                        <div><div style="font-size: 0.55rem; color: #a08060; text-transform: uppercase;">Avg / Day</div><div style="font-size: 0.85rem; font-weight: 700; color: #3d2314;">$54</div><div style="font-size: 0.55rem; color: #6b9e3a;">↑ vs prev 90d</div></div>
                        <div><div style="font-size: 0.55rem; color: #a08060; text-transform: uppercase;">Best Weekday</div><div style="font-size: 0.85rem; font-weight: 700; color: #3d2314;">Saturday</div><div style="font-size: 0.55rem; color: #a08060;">$98 avg</div></div>
                    </div>
                @endif
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
                @if (($widget['size'] ?? 'sm') === 'md')
                    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 8px;">
                        <div class="pw-stat" style="flex-direction: column; align-items: flex-start;"><span class="pw-stat-label">New this week</span><span class="pw-stat-value">4</span></div>
                        <div class="pw-stat" style="flex-direction: column; align-items: flex-start;"><span class="pw-stat-label">Repeat rate</span><span class="pw-stat-value">62%</span></div>
                        <div class="pw-stat" style="flex-direction: column; align-items: flex-start;"><span class="pw-stat-label">Avg LTV</span><span class="pw-stat-value">$184</span></div>
                        <div class="pw-stat" style="flex-direction: column; align-items: flex-start;"><span class="pw-stat-label">Total active</span><span class="pw-stat-value">128</span></div>
                    </div>
                @else
                    <div class="pw-stat"><span class="pw-stat-label">New this week</span><span class="pw-stat-value">4</span></div>
                    <div class="pw-stat" style="margin-top: 8px;"><span class="pw-stat-label">Repeat rate</span><span class="pw-stat-value">62%</span></div>
                @endif
                @break
            @case('weekly_revenue')
                @php
                    // md = this week only, lg = + last week overlay, xl = + 4-week trend below.
                    $size = $widget['size'] ?? 'md';
                    $thisWeek = [50, 70, 45, 80, 65, 90, 55];
                    $lastWeek = [40, 55, 60, 65, 50, 75, 60];
                    $isComparison = in_array($size, ['lg', 'xl'], true);
                    $weekdayLabels = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
                @endphp
                @if ($isComparison)
                    <div style="display: flex; gap: 10px; font-size: 0.6rem; color: #a08060; margin-bottom: 4px;">
                        <span><span class="pw-dot" style="background: var(--accent);"></span>This week · $455</span>
                        <span><span class="pw-dot" style="background: var(--border-medium);"></span>Last week · $405</span>
                    </div>
                @endif
                <div class="pw-line" @if ($size === 'xl') style="height: 60px;" @endif>
                    @foreach ($thisWeek as $i => $h)
                        @if ($isComparison)
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
                    @foreach ($weekdayLabels as $label)
                        <span>{{ $label }}</span>
                    @endforeach
                </div>
                @if ($size === 'xl')
                    <div style="margin-top: 14px; padding-top: 12px; border-top: 1px solid var(--border-subtle);">
                        <div style="font-size: 0.55rem; color: #a08060; text-transform: uppercase; margin-bottom: 6px;">4-week trend</div>
                        <div class="pw-line" style="height: 32px;">
                            @foreach ([60, 75, 80, 95] as $i => $h)
                                <div style="flex: 1; padding: 0 4px; display: flex; flex-direction: column; align-items: center; gap: 2px;">
                                    <div class="pw-line-bar" style="height: {{ $h }}%; background: var(--accent); width: 100%;"></div>
                                    <div style="font-size: 0.55rem; color: #a08060;">Wk {{ $i + 1 }}</div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
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
                @php
                    $items = ($widget['size'] ?? 'md') === 'lg'
                        ? ['Chocolate Cake ×2' => true, 'Banana Bread ×4' => true, 'Sugar Cookies ×24' => false, 'Croissants ×12' => false, 'Sourdough ×6' => false, 'Muffins ×8' => false]
                        : ['Chocolate Cake ×2' => true, 'Banana Bread ×4' => true, 'Sugar Cookies ×24' => false];
                    $done = count(array_filter($items));
                @endphp
                @if (($widget['size'] ?? 'md') === 'lg')
                    <div class="pw-stat" style="margin-bottom: 6px;"><span class="pw-stat-label">Progress</span><span class="pw-stat-value">{{ $done }}/{{ count($items) }}</span></div>
                @endif
                @foreach ($items as $item => $isDone)
                    <div class="pw-row">
                        <span @if ($isDone) style="color: #a08060; text-decoration: line-through;" @endif>{{ $item }}</span>
                        <span style="color: {{ $isDone ? '#6b9e3a' : '#d4a574' }};">{{ $isDone ? '●' : '○' }}</span>
                    </div>
                @endforeach
                @break
            @case('inbox')
                <div class="pw-stat"><span class="pw-stat-label">Unread</span><span class="pw-stat-value">3</span></div>
                @if (($widget['size'] ?? 'sm') === 'md')
                    @foreach (['Sarah M.' => 'Can I add to my order?', 'Mike R.' => 'Question about delivery time', 'Lisa K.' => 'Cake feedback - thanks!'] as $name => $msg)
                        <div class="pw-row"><span style="color: #6b4c3b; font-weight: 600;">{{ $name }}</span><span style="color: #a08060; font-style: italic;">{{ \Illuminate\Support\Str::limit($msg, 24) }}</span></div>
                    @endforeach
                @else
                    <div style="font-size: 0.65rem; color: #a08060; margin-top: 4px;">Latest: "Can I add to my order?"</div>
                @endif
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
                @php
                    $coupons = ($widget['size'] ?? 'sm') === 'md'
                        ? ['WELCOME10' => 23, 'SPRING20' => 8, 'BDAY15' => 12, 'LOYAL5' => 6]
                        : ['WELCOME10' => 23, 'SPRING20' => 8];
                @endphp
                <div class="pw-stat"><span class="pw-stat-label">Active</span><span class="pw-stat-value">4</span></div>
                @foreach ($coupons as $code => $uses)
                    <div class="pw-row" @if ($loop->first) style="margin-top: 6px;" @endif><span>{{ $code }}</span><span>{{ $uses }} uses</span></div>
                @endforeach
                @break
            @case('gift_card_balance')
                <div class="pw-stat"><span class="pw-stat-label">Outstanding</span><span class="pw-stat-value">$340</span></div>
                <div style="font-size: 0.65rem; color: #a08060; margin-top: 4px;">12 active cards</div>
                @break
            @case('loyalty_leaders')
                @php
                    $leaders = ($widget['size'] ?? 'sm') === 'md'
                        ? ['Sarah M.' => '520 pts', 'Mike R.' => '380 pts', 'Lisa K.' => '290 pts', 'Emma T.' => '240 pts', 'James P.' => '195 pts']
                        : ['Sarah M.' => '520 pts', 'Mike R.' => '380 pts', 'Lisa K.' => '290 pts'];
                @endphp
                @foreach ($leaders as $name => $pts)
                    <div class="pw-row"><span>{{ $loop->iteration }}. {{ $name }}</span><span>{{ $pts }}</span></div>
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
                @if (($widget['size'] ?? 'sm') === 'md')
                    <div style="margin-top: 8px; padding: 6px 8px; background: #fdf8f2; border-radius: 6px;">
                        <div style="font-size: 0.55rem; color: #a08060; text-transform: uppercase;">Latest Inquiry</div>
                        <div style="font-size: 0.7rem; font-weight: 600; color: #3d2314;">Henderson Wedding</div>
                        <div style="font-size: 0.6rem; color: #6b4c3b;">Wedding — Jun 12 — 80 guests</div>
                    </div>
                @endif
                @break
            @case('seasonal_items')
                <div class="pw-row"><span style="color: #6b9e3a;">● Coming Soon</span><span>2</span></div>
                <div class="pw-row"><span style="color: #d4574a;">● Ending Soon</span><span>1</span></div>
                <div class="pw-row"><span style="color: #d4a574;">● In Season</span><span>5</span></div>
                @if (($widget['size'] ?? 'sm') === 'md')
                    <div style="margin-top: 8px; padding-top: 6px; border-top: 1px solid var(--border-subtle); font-size: 0.6rem; color: #6b4c3b; line-height: 1.4;">
                        <div><span style="color: #d4574a;">↓</span> Easter Eggs ends in 3 days</div>
                        <div><span style="color: #6b9e3a;">↑</span> Pumpkin Bread starts Sep 1</div>
                    </div>
                @endif
                @break
            @case('review_summary')
                <div style="display: flex; align-items: baseline; gap: 6px;">
                    <span style="font-size: 1.2rem; font-weight: 700; color: #3d2314;">4.8</span>
                    <span style="font-size: 0.75rem; color: #e8b04a;">★★★★★</span>
                </div>
                <div style="font-size: 0.6rem; color: #a08060; margin-top: 2px;">28 reviews</div>
                @if (($widget['size'] ?? 'sm') === 'md')
                    <div style="margin-top: 8px; padding: 6px 8px; background: #fdf8f2; border-radius: 6px;">
                        <div style="font-size: 0.55rem; color: #a08060; text-transform: uppercase;">Latest Review</div>
                        <div style="font-size: 0.65rem; color: #6b4c3b; font-style: italic; line-height: 1.4;">"Best chocolate cake I've ever had! Will be back."</div>
                        <div style="font-size: 0.55rem; color: #a08060; margin-top: 2px;">— Sarah M., 2 days ago</div>
                    </div>
                @endif
                @break
            @case('reorder_reminders')
                <div class="pw-stat"><span class="pw-stat-label">Lapsed</span><span class="pw-stat-value">6</span></div>
                <div style="font-size: 0.6rem; color: #a08060; margin-top: 4px;">Haven't ordered in 30+ days</div>
                @break
            @case('low_stock')
                @php
                    $items = ($widget['size'] ?? 'sm') === 'md'
                        ? ['Flour' => '2 lbs', 'Butter' => '0 lbs', 'Vanilla' => '1 oz', 'Sugar' => '3 lbs', 'Cocoa' => '8 oz']
                        : ['Flour' => '2 lbs', 'Butter' => '0 lbs', 'Vanilla' => '1 oz'];
                @endphp
                @if (($widget['size'] ?? 'sm') === 'md')
                    <div class="pw-stat" style="margin-bottom: 6px;"><span class="pw-stat-label">Items at risk</span><span class="pw-stat-value">5</span></div>
                @endif
                @foreach ($items as $item => $remaining)
                    <div class="pw-row"><span>{{ $item }}</span><span style="color: #d4574a;">{{ $remaining }}</span></div>
                @endforeach
                @break
            @case('at_risk_customers')
                @php
                    $atRisk = ($widget['size'] ?? 'md') === 'lg'
                        ? ['Sarah M.' => '45d ago', 'Mike R.' => '38d ago', 'Lisa K.' => '32d ago', 'Emma T.' => '28d ago', 'James P.' => '24d ago']
                        : ['Sarah M.' => '45d ago', 'Mike R.' => '38d ago', 'Lisa K.' => '32d ago'];
                @endphp
                @foreach ($atRisk as $name => $when)
                    <div class="pw-row"><span>{{ $name }}</span><span>{{ $when }}</span></div>
                @endforeach
                @break
            @case('birthday')
                @php
                    $birthdays = ($widget['size'] ?? 'sm') === 'md'
                        ? ['Emma T.' => 'Tomorrow', 'James P.' => 'In 3 days', 'Olivia C.' => 'In 5 days', 'Liam B.' => 'In 8 days', 'Ava S.' => 'In 12 days']
                        : ['Emma T.' => 'Tomorrow', 'James P.' => 'In 3 days', 'Olivia C.' => 'In 5 days'];
                @endphp
                @foreach ($birthdays as $name => $when)
                    <div class="pw-row"><span>🎂 {{ $name }}</span><span>{{ $when }}</span></div>
                @endforeach
                @break
            @case('recent_activity')
                @php
                    $events = ($widget['size'] ?? 'md') === 'lg'
                        ? ['2m ago' => 'New order #142', '15m ago' => 'Product updated', '1h ago' => 'Customer signed up', '2h ago' => 'Coupon WELCOME10 redeemed', '3h ago' => 'Review left — 5 stars', '4h ago' => 'Inventory adjusted']
                        : ['2m ago' => 'New order #142', '15m ago' => 'Product updated', '1h ago' => 'Customer signed up'];
                @endphp
                @foreach ($events as $when => $what)
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
