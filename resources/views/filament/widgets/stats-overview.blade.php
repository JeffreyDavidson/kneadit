@php
    $cards = $this->getCards();
    $isXl = $this->isSize('xl');
    $sparklineHeight = $isXl ? 36 : 18;
@endphp

<x-admin.dashboard.preview-card heading="Today's Snapshot" icon="📊">
    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px;">
        @foreach ($cards as $card)
            <div style="background: var(--pw-card-grad-start); border-radius: 8px; padding: 8px;">
                <div class="pw-stat-label">{{ $card['label'] }}</div>
                <div style="font-size: 1.4rem; font-weight: 700; color: var(--pw-card-text); line-height: 1.1; margin-top: 2px;">{{ $card['value'] }}</div>
                <div style="font-size: 0.6rem; color: {{ $card['tone'] }}; margin-top: 2px;">{{ $card['delta'] }}</div>
                <x-admin.dashboard.spark-bars :bars="$card['chart']" :height="$sparklineHeight" class="mt-1" />
            </div>
        @endforeach
    </div>

    @if ($isXl)
        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 6px; margin-top: 12px; padding-top: 12px; border-top: 1px solid var(--pw-card-border-subtle);">
            @foreach ($cards as $card)
                <div style="text-align: center;">
                    <div style="font-size: 0.55rem; color: var(--pw-card-text-muted); text-transform: uppercase;">7-day</div>
                    <div style="font-size: 0.7rem; color: var(--pw-card-text); font-weight: 600;">avg {{ count($card['chart']) > 0 ? (int) round(array_sum($card['chart']) / count($card['chart'])) : 0 }}%</div>
                </div>
            @endforeach
        </div>
    @endif
</x-admin.dashboard.preview-card>
