<x-filament-widgets::widget>
    <x-filament::section heading="Seasonal Items" icon="heroicon-o-sun">
        @php
            $inSeason = $this->getCurrentlyInSeasonCount();
            $comingSoon = $this->getComingSoon();
            $endingSoon = $this->getEndingSoon();
        @endphp

        <x-admin.stat-cell label="Currently In Season" class="mb-4">{{ $inSeason }}</x-admin.stat-cell>

        {{-- sm: in-season count only. md: + coming-soon and ending-soon side-by-side lists. --}}
        @unless ($this->isSize('sm'))
            <div class="grid grid-cols-2 gap-3">
                @foreach ([
                    ['title' => 'Coming Soon', 'items' => $comingSoon, 'bg' => 'bg-brand-300/20', 'empty' => 'None upcoming'],
                    ['title' => 'Ending Soon', 'items' => $endingSoon, 'bg' => 'bg-golden/20', 'empty' => 'None ending soon'],
                ] as $group)
                    <div>
                        <div class="text-xs font-semibold text-brand-700 mb-2">{{ $group['title'] }}</div>
                        @if (count($group['items']) > 0)
                            @foreach ($group['items'] as $item)
                                <div class="px-2.5 py-1.5 {{ $group['bg'] }} rounded-md mb-1 text-xs">
                                    <div class="font-semibold text-brand-900">{{ $item['name'] }}</div>
                                    <div class="text-brand-600">{{ $item['date'] }}</div>
                                </div>
                            @endforeach
                        @else
                            <div class="text-brand-600 italic text-xs p-1.5">{{ $group['empty'] }}</div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endunless
    </x-filament::section>
</x-filament-widgets::widget>
