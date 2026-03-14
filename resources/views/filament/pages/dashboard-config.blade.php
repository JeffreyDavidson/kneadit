<x-filament-panels::page>
    <style>
        .config-layout { display: grid; grid-template-columns: 340px 1fr; gap: 28px; min-height: 600px; }
        @media (max-width: 1024px) { .config-layout { grid-template-columns: 1fr; } }

        /* Left panel — widget list */
        .widget-panel { background: #fdf8f2; border: 1px solid rgba(212,165,116,0.2); border-radius: 16px; padding: 20px; }
        .widget-panel-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; padding-bottom: 12px; border-bottom: 1px solid rgba(212,165,116,0.15); }
        .widget-panel-title { font-size: 0.85rem; font-weight: 700; color: #3d2314; text-transform: uppercase; letter-spacing: 0.5px; margin: 0; }

        .widget-list { display: flex; flex-direction: column; gap: 4px; }
        .widget-card {
            display: flex; align-items: center; gap: 10px;
            padding: 10px 12px; border-radius: 10px;
            border: 1px solid transparent; background: white;
            cursor: grab; user-select: none;
            transition: all 0.15s;
        }
        .widget-card:hover { border-color: rgba(212,165,116,0.3); box-shadow: 0 1px 4px rgba(61,35,20,0.06); }
        .widget-card:active { cursor: grabbing; }
        .widget-card.disabled { opacity: 0.4; background: #f8f8f8; }
        .widget-card.sortable-ghost { opacity: 0.2; }
        .widget-card.sortable-drag { box-shadow: 0 6px 20px rgba(61,35,20,0.12); z-index: 10; }

        .widget-drag { color: #d4c4b0; flex-shrink: 0; }
        .widget-icon { font-size: 1.1rem; flex-shrink: 0; }
        .widget-info { flex: 1; min-width: 0; }
        .widget-name { font-weight: 600; color: #3d2314; margin: 0; font-size: 0.85rem; line-height: 1.2; }
        .widget-desc { font-size: 0.7rem; color: #a08060; margin: 1px 0 0; }

        .span-selector { display: flex; gap: 1px; flex-shrink: 0; background: rgba(212,165,116,0.15); border-radius: 6px; overflow: hidden; }
        .span-btn {
            width: 24px; height: 24px; border: none;
            background: white; color: #a08060; font-size: 0.7rem; font-weight: 700;
            cursor: pointer; transition: all 0.15s; display: flex; align-items: center; justify-content: center;
        }
        .span-btn:hover { background: #fdf8f2; color: #6b4c3b; }
        .span-btn.active { background: #d4a574; color: white; }

        .toggle-mini {
            width: 36px; height: 20px; border-radius: 10px; border: none;
            cursor: pointer; transition: background 0.2s; flex-shrink: 0; position: relative;
        }
        .toggle-mini.on { background: #d4a574; }
        .toggle-mini.off { background: #d0d0d0; }
        .toggle-mini span {
            display: block; width: 16px; height: 16px; border-radius: 50%;
            background: white; box-shadow: 0 1px 2px rgba(0,0,0,0.1);
            position: absolute; top: 2px; transition: left 0.15s;
        }
        .toggle-mini.on span { left: 18px; }
        .toggle-mini.off span { left: 2px; }

        /* Right panel — preview */
        .preview-panel { background: #f5f0ea; border: 1px solid rgba(212,165,116,0.15); border-radius: 16px; padding: 20px; overflow: hidden; }
        .preview-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; padding-bottom: 12px; border-bottom: 1px solid rgba(212,165,116,0.12); }
        .preview-title { font-size: 0.85rem; font-weight: 700; color: #3d2314; text-transform: uppercase; letter-spacing: 0.5px; margin: 0; }
        .preview-badge { font-size: 0.65rem; background: rgba(212,165,116,0.2); color: #8b6844; padding: 3px 8px; border-radius: 6px; font-weight: 600; }

        .preview-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; }

        .preview-widget {
            border-radius: 10px; overflow: hidden;
            border: 1px solid rgba(212,165,116,0.15);
            background: white;
            transition: all 0.2s;
        }
        .preview-widget-header {
            background: linear-gradient(135deg, #6b4c3b, #8b6844);
            padding: 8px 12px;
            display: flex; align-items: center; gap: 6px;
        }
        .preview-widget-header span { color: white; font-size: 0.75rem; font-weight: 600; }
        .preview-widget-header .pw-icon { font-size: 0.85rem; }
        .preview-widget-body { padding: 12px; min-height: 50px; }

        /* Simulated content */
        .pw-stat { display: flex; justify-content: space-between; align-items: baseline; }
        .pw-stat-value { font-size: 1.4rem; font-weight: 700; color: #3d2314; }
        .pw-stat-label { font-size: 0.65rem; color: #a08060; text-transform: uppercase; }
        .pw-bar { height: 6px; border-radius: 3px; background: rgba(212,165,116,0.15); margin-top: 6px; }
        .pw-bar-fill { height: 100%; border-radius: 3px; background: linear-gradient(90deg, #d4a574, #e8b04a); }
        .pw-line { height: 40px; display: flex; align-items: end; gap: 2px; }
        .pw-line-bar { flex: 1; background: rgba(212,165,116,0.3); border-radius: 2px 2px 0 0; min-height: 4px; }
        .pw-row { display: flex; justify-content: space-between; padding: 4px 0; border-bottom: 1px solid rgba(212,165,116,0.08); font-size: 0.7rem; color: #6b4c3b; }
        .pw-row:last-child { border: none; }
        .pw-dot { width: 6px; height: 6px; border-radius: 50%; display: inline-block; margin-right: 4px; }

        .config-footer {
            display: flex; align-items: center; justify-content: space-between;
            padding-top: 20px; margin-top: 24px;
            border-top: 1px solid rgba(212,165,116,0.2);
        }
        .config-footer a {
            font-size: 0.9rem; color: #8b6844; text-decoration: none;
            display: flex; align-items: center; gap: 6px;
        }
        .config-footer a:hover { color: #3d2314; }
    </style>

    <div class="config-layout">
        {{-- Left: Widget List --}}
        <div class="widget-panel">
            <div class="widget-panel-header">
                <p class="widget-panel-title">Widgets</p>
                <x-filament::button size="xs" color="gray" wire:click="resetDefaults">Reset</x-filament::button>
            </div>

            <div class="widget-list" id="widget-sortable">
                @foreach($widgets as $index => $widget)
                    <div class="widget-card {{ $widget['visible'] ? '' : 'disabled' }}" data-index="{{ $index }}">
                        <div class="widget-drag">
                            <svg xmlns="http://www.w3.org/2000/svg" style="width: 14px; height: 14px;" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M7 2a2 2 0 1 0 0 4 2 2 0 0 0 0-4zM7 8a2 2 0 1 0 0 4 2 2 0 0 0 0-4zM7 14a2 2 0 1 0 0 4 2 2 0 0 0 0-4zM13 2a2 2 0 1 0 0 4 2 2 0 0 0 0-4zM13 8a2 2 0 1 0 0 4 2 2 0 0 0 0-4zM13 14a2 2 0 1 0 0 4 2 2 0 0 0 0-4z"/>
                            </svg>
                        </div>
                        <div class="widget-icon">{{ $widget['icon'] }}</div>
                        <div class="widget-info">
                            <p class="widget-name">{{ $widget['name'] }}</p>
                            <p class="widget-desc">{{ $widget['description'] }}</p>
                        </div>
                        <div class="span-selector">
                            @for($s = 1; $s <= 3; $s++)
                                <button class="span-btn {{ ($widget['span'] ?? 1) == $s ? 'active' : '' }}"
                                        wire:click="setSpan({{ $index }}, {{ $s }})" type="button"
                                        title="{{ $s }}/3 width">{{ $s }}</button>
                            @endfor
                        </div>
                        <button class="toggle-mini {{ $widget['visible'] ? 'on' : 'off' }}"
                                wire:click="toggleWidget({{ $index }})" type="button">
                            <span></span>
                        </button>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Right: Live Preview --}}
        <div class="preview-panel">
            <div class="preview-header">
                <p class="preview-title">Preview</p>
                <span class="preview-badge">Live Preview</span>
            </div>

            <div class="preview-grid">
                @foreach($widgets as $widget)
                    @if($widget['visible'])
                        <div class="preview-widget" style="grid-column: span {{ $widget['span'] ?? 1 }};">
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
                                            @foreach(['Orders: 5', 'Pending: 2', 'Revenue: $142'] as $stat)
                                                <div style="background: #fdf8f2; border-radius: 6px; padding: 6px 8px;">
                                                    <div style="font-size: 0.6rem; color: #a08060;">{{ explode(':', $stat)[0] }}</div>
                                                    <div style="font-size: 0.85rem; font-weight: 700; color: #3d2314;">{{ trim(explode(':', $stat)[1]) }}</div>
                                                </div>
                                            @endforeach
                                        </div>
                                        @break
                                    @case('revenue_chart')
                                        <div class="pw-line">
                                            @foreach([30, 45, 20, 60, 80, 55, 70, 40, 90, 65, 50, 75] as $h)
                                                <div class="pw-line-bar" style="height: {{ $h }}%;"></div>
                                            @endforeach
                                        </div>
                                        @break
                                    @case('recent_orders')
                                    @case('upcoming_orders')
                                        @for($i = 0; $i < 3; $i++)
                                            <div class="pw-row">
                                                <span><span class="pw-dot" style="background: {{ ['#d4a574','#e8b04a','#8b6844'][$i] }};"></span>Order #{{ 100 + $i }}</span>
                                                <span>${{ [28, 45, 32][$i] }}</span>
                                            </div>
                                        @endfor
                                        @break
                                    @case('top_products')
                                        @foreach(['Chocolate Cake' => 85, 'Banana Bread' => 60, 'Cookies' => 40] as $name => $pct)
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
                                            @foreach([50, 70, 45, 80, 65, 90, 55] as $h)
                                                <div class="pw-line-bar" style="height: {{ $h }}%;"></div>
                                            @endforeach
                                        </div>
                                        <div style="display: flex; justify-content: space-between; font-size: 0.6rem; color: #a08060; margin-top: 4px;">
                                            <span>Mon</span><span>Thu</span><span>Sun</span>
                                        </div>
                                        @break
                                    @case('order_funnel')
                                        @foreach(['Pending' => '#e8b04a', 'Confirmed' => '#d4a574', 'Delivered' => '#8b6844'] as $status => $color)
                                            <div class="pw-row">
                                                <span><span class="pw-dot" style="background: {{ $color }};"></span>{{ $status }}</span>
                                                <span>{{ ['Pending' => 3, 'Confirmed' => 5, 'Delivered' => 12][$status] }}</span>
                                            </div>
                                        @endforeach
                                        @break
                                    @case('todays_orders')
                                        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 6px;">
                                            @foreach(['9:00 AM' => '$28', '11:30 AM' => '$45', '2:00 PM' => '$32'] as $time => $amt)
                                                <div style="background: #fdf8f2; border-radius: 6px; padding: 6px 8px;">
                                                    <div style="font-size: 0.6rem; color: #a08060;">{{ $time }}</div>
                                                    <div style="font-size: 0.8rem; font-weight: 700; color: #3d2314;">{{ $amt }}</div>
                                                </div>
                                            @endforeach
                                        </div>
                                        @break
                                    @case('baking_sheet')
                                        @foreach(['Chocolate Cake ×2', 'Banana Bread ×4', 'Sugar Cookies ×24'] as $item)
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
                                    @default
                                        <div style="display: flex; flex-direction: column; gap: 4px;">
                                            @for($i = 0; $i < 2; $i++)
                                                <div style="height: 8px; border-radius: 4px; background: rgba(212,165,116,0.12); width: {{ [100, 70][$i] }}%;"></div>
                                            @endfor
                                        </div>
                                @endswitch
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    </div>

    <div class="config-footer">
        <a href="{{ route('filament.admin.pages.dashboard') }}">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" style="width: 16px; height: 16px;"><path fill-rule="evenodd" d="M11.78 5.22a.75.75 0 0 1 0 1.06L8.06 10l3.72 3.72a.75.75 0 1 1-1.06 1.06l-4.25-4.25a.75.75 0 0 1 0-1.06l4.25-4.25a.75.75 0 0 1 1.06 0Z" clip-rule="evenodd" /></svg>
            Back to Dashboard
        </a>
        <x-filament::button wire:click="save">
            Save Layout
        </x-filament::button>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.6/Sortable.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const el = document.getElementById('widget-sortable');
            if (!el) return;
            Sortable.create(el, {
                animation: 200,
                ghostClass: 'sortable-ghost',
                dragClass: 'sortable-drag',
                onEnd: function(evt) {
                    if (evt.oldIndex === evt.newIndex) return;
                    @this.call('reorder', evt.oldIndex, evt.newIndex);
                }
            });
        });
    </script>
</x-filament-panels::page>
