@php
    $inSeason = $this->getCurrentlyInSeasonCount();
    $comingSoon = $this->getComingSoon();
    $endingSoon = $this->getEndingSoon();
@endphp

<x-admin.dashboard.preview-card heading="Seasonal Items" icon="heroicon-o-sparkles">
    <x-admin.dashboard.stat-row label="Currently In Season" :value="$inSeason" class="mb-3" />

    @unless ($this->isSize('sm'))
        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; margin-top: 8px; padding-top: 8px; border-top: 1px solid var(--pw-card-border-subtle);">
            @foreach ([
                ['title' => 'Coming Soon', 'items' => $comingSoon, 'color' => '#6b9e3a', 'empty' => 'None upcoming'],
                ['title' => 'Ending Soon', 'items' => $endingSoon, 'color' => '#d4574a', 'empty' => 'None ending soon'],
            ] as $group)
                <div>
                    <div style="font-size: 0.55rem; color: var(--pw-card-text-muted); text-transform: uppercase; margin-bottom: 4px;">
                        <span style="color: {{ $group['color'] }}; margin-right: 4px;">●</span>{{ $group['title'] }}
                    </div>
                    @if (count($group['items']) > 0)
                        @foreach ($group['items'] as $item)
                            <div style="font-size: 0.7rem; color: var(--pw-card-text); margin-bottom: 4px;">
                                <div style="font-weight: 600;">{{ $item['name'] }}</div>
                                <div style="font-size: 0.6rem; color: var(--pw-card-text-muted);">{{ $item['date'] }}</div>
                            </div>
                        @endforeach
                    @else
                        <div style="font-size: 0.7rem; color: var(--pw-card-text-muted); font-style: italic;">{{ $group['empty'] }}</div>
                    @endif
                </div>
            @endforeach
        </div>
    @endunless
</x-admin.dashboard.preview-card>
