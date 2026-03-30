<x-filament-widgets::widget>
    <x-filament::section heading="Seasonal Items" icon="heroicon-o-sun">
        @php
            $inSeason = $this->getCurrentlyInSeasonCount();
            $comingSoon = $this->getComingSoon();
            $endingSoon = $this->getEndingSoon();
        @endphp

        <div style="background: #fdf8f2; border-radius: 8px; padding: 12px; text-align: center; margin-bottom: 16px;">
            <div style="font-size: 1.5rem; font-weight: 700; color: #3d2314;">{{ $inSeason }}</div>
            <div style="font-size: 0.75rem; color: #6b4c3b;">Currently In Season</div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
            <div>
                <div style="font-size: 0.75rem; font-weight: 600; color: #6b4c3b; margin-bottom: 8px;">Coming Soon</div>
                @if (count($comingSoon) > 0)
                    @foreach ($comingSoon as $item)
                        <div style="padding: 6px 10px; background: #d4a57420; border-radius: 6px; margin-bottom: 4px; font-size: 0.75rem;">
                            <div style="font-weight: 600; color: #3d2314;">{{ $item['name'] }}</div>
                            <div style="color: #8b6844;">{{ $item['date'] }}</div>
                        </div>
                    @endforeach
                @else
                    <div style="color: #8b6844; font-style: italic; font-size: 0.75rem; padding: 6px;">None upcoming</div>
                @endif
            </div>
            <div>
                <div style="font-size: 0.75rem; font-weight: 600; color: #6b4c3b; margin-bottom: 8px;">Ending Soon</div>
                @if (count($endingSoon) > 0)
                    @foreach ($endingSoon as $item)
                        <div style="padding: 6px 10px; background: #e8b04a20; border-radius: 6px; margin-bottom: 4px; font-size: 0.75rem;">
                            <div style="font-weight: 600; color: #3d2314;">{{ $item['name'] }}</div>
                            <div style="color: #8b6844;">{{ $item['date'] }}</div>
                        </div>
                    @endforeach
                @else
                    <div style="color: #8b6844; font-style: italic; font-size: 0.75rem; padding: 6px;">None ending soon</div>
                @endif
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
