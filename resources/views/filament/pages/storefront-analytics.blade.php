<x-filament-panels::page>
    <div>
        {{-- Time Filter --}}
        <div style="display: flex; gap: 8px; margin-bottom: 24px;">
            @foreach (['today' => 'Today', 'week' => 'This Week', 'month' => 'This Month', 'all' => 'All Time'] as $key => $label)
                <button
                    wire:click="setPeriod('{{ $key }}')"
                    style="padding: 8px 18px; border-radius: 8px; border: 2px solid {{ $this->period === $key ? '#b45309' : '#e5e7eb' }}; background: {{ $this->period === $key ? '#fef3c7' : '#fff' }}; color: {{ $this->period === $key ? '#92400e' : '#6b7280' }}; font-weight: {{ $this->period === $key ? '700' : '500' }}; cursor: pointer; font-size: 14px; transition: all 0.2s;"
                >
                    {{ $label }}
                </button>
            @endforeach
        </div>

        {{-- Overview Cards --}}
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 32px;">
            <div style="background: linear-gradient(135deg, #fef3c7, #fde68a); border-radius: 12px; padding: 20px; border: 1px solid #f59e0b;">
                <div style="font-size: 13px; color: #92400e; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Total Views</div>
                <div style="font-size: 32px; font-weight: 800; color: #78350f; margin-top: 4px;">{{ number_format($this->getTotalViews()) }}</div>
            </div>
            <div style="background: linear-gradient(135deg, #fce7f3, #fbcfe8); border-radius: 12px; padding: 20px; border: 1px solid #ec4899;">
                <div style="font-size: 13px; color: #9d174d; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Unique Visitors</div>
                <div style="font-size: 32px; font-weight: 800; color: #831843; margin-top: 4px;">{{ number_format($this->getUniqueVisitors()) }}</div>
            </div>
            <div style="background: linear-gradient(135deg, #dbeafe, #bfdbfe); border-radius: 12px; padding: 20px; border: 1px solid #3b82f6;">
                <div style="font-size: 13px; color: #1e40af; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Most Popular Page</div>
                <div style="font-size: 32px; font-weight: 800; color: #1e3a8a; margin-top: 4px; text-transform: capitalize;">{{ $this->getMostPopularPage() }}</div>
            </div>
            <div style="background: linear-gradient(135deg, #d1fae5, #a7f3d0); border-radius: 12px; padding: 20px; border: 1px solid #10b981;">
                <div style="font-size: 13px; color: #065f46; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Conversion Rate</div>
                <div style="font-size: 32px; font-weight: 800; color: #064e3b; margin-top: 4px;">{{ $this->getConversionRate() }}%</div>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 32px;">
            {{-- Page Views Chart --}}
            <div style="background: #fff; border-radius: 12px; padding: 24px; border: 1px solid #e5e7eb; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                <h3 style="font-size: 16px; font-weight: 700; color: #1f2937; margin: 0 0 16px 0;">📊 Views by Page</h3>
                @php $pageViews = $this->getPageViewsChart(); $maxPageViews = $pageViews->max('views') ?: 1; @endphp
                @forelse ($pageViews as $pv)
                    <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
                        <div style="width: 70px; font-size: 13px; font-weight: 600; color: #4b5563; text-transform: capitalize;">{{ $pv->page }}</div>
                        <div style="flex: 1; background: #f3f4f6; border-radius: 6px; height: 28px; overflow: hidden;">
                            <div style="width: {{ ($pv->views / $maxPageViews) * 100 }}%; background: linear-gradient(90deg, #f59e0b, #d97706); height: 100%; border-radius: 6px; display: flex; align-items: center; padding-left: 8px; min-width: 30px;">
                                <span style="font-size: 12px; font-weight: 700; color: #fff;">{{ number_format($pv->views) }}</span>
                            </div>
                        </div>
                    </div>
                @empty
                    <p style="color: #9ca3af; font-size: 14px;">No page views recorded yet.</p>
                @endforelse
            </div>

            {{-- Conversion Funnel --}}
            <div style="background: #fff; border-radius: 12px; padding: 24px; border: 1px solid #e5e7eb; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                <h3 style="font-size: 16px; font-weight: 700; color: #1f2937; margin: 0 0 16px 0;">🔄 Conversion Funnel</h3>
                @php $funnel = $this->getConversionFunnel(); @endphp
                @foreach ($funnel as $i => $step)
                    <div style="margin-bottom: 12px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                            <span style="font-size: 14px; font-weight: 600; color: #374151;">{{ $step['label'] }}</span>
                            <span style="font-size: 14px; font-weight: 700; color: #1f2937;">{{ number_format($step['count']) }}</span>
                        </div>
                        <div style="background: #f3f4f6; border-radius: 6px; height: 24px; overflow: hidden;">
                            <div style="width: {{ $step['percentage'] }}%; background: linear-gradient(90deg, {{ ['#f59e0b','#f97316','#ef4444','#10b981'][$i] }}, {{ ['#d97706','#ea580c','#dc2626','#059669'][$i] }}); height: 100%; border-radius: 6px; min-width: {{ $step['count'] > 0 ? '4px' : '0' }};"></div>
                        </div>
                        @if ($step['dropoff'] !== null)
                            <div style="font-size: 11px; color: #ef4444; margin-top: 2px;">↓ {{ $step['dropoff'] }}% drop-off</div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Daily Trend --}}
        <div style="background: #fff; border-radius: 12px; padding: 24px; border: 1px solid #e5e7eb; box-shadow: 0 1px 3px rgba(0,0,0,0.05); margin-bottom: 32px;">
            <h3 style="font-size: 16px; font-weight: 700; color: #1f2937; margin: 0 0 16px 0;">📈 Daily Views (Last 30 Days)</h3>
            @php $daily = $this->getDailyTrend(); $maxDaily = $daily->max('views') ?: 1; @endphp
            <div style="display: flex; align-items: flex-end; gap: 3px; height: 160px;">
                @forelse ($daily as $day)
                    <div style="flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: flex-end; height: 100%;" title="{{ $day->date }}: {{ $day->views }} views">
                        <div style="width: 100%; background: linear-gradient(to top, #f59e0b, #fbbf24); border-radius: 3px 3px 0 0; height: {{ max(($day->views / $maxDaily) * 100, 2) }}%;"></div>
                    </div>
                @empty
                    <p style="color: #9ca3af; font-size: 14px;">No data yet.</p>
                @endforelse
            </div>
            @if ($daily->isNotEmpty())
                <div style="display: flex; justify-content: space-between; margin-top: 6px;">
                    <span style="font-size: 11px; color: #9ca3af;">{{ $daily->first()?->date }}</span>
                    <span style="font-size: 11px; color: #9ca3af;">{{ $daily->last()?->date }}</span>
                </div>
            @endif
        </div>

        {{-- Top Products --}}
        @php $topProducts = $this->getTopProducts(); @endphp
        @if ($topProducts->isNotEmpty())
            <div style="background: #fff; border-radius: 12px; padding: 24px; border: 1px solid #e5e7eb; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                <h3 style="font-size: 16px; font-weight: 700; color: #1f2937; margin: 0 0 16px 0;">🧁 Top Products Viewed</h3>
                @php $maxProduct = $topProducts->max('views') ?: 1; @endphp
                @foreach ($topProducts as $product)
                    <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
                        <div style="width: 140px; font-size: 13px; font-weight: 600; color: #4b5563; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $product->name }}</div>
                        <div style="flex: 1; background: #f3f4f6; border-radius: 6px; height: 24px; overflow: hidden;">
                            <div style="width: {{ ($product->views / $maxProduct) * 100 }}%; background: linear-gradient(90deg, #ec4899, #be185d); height: 100%; border-radius: 6px; display: flex; align-items: center; padding-left: 8px; min-width: 30px;">
                                <span style="font-size: 11px; font-weight: 700; color: #fff;">{{ number_format($product->views) }}</span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</x-filament-panels::page>
