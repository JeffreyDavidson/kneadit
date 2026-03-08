<x-filament-widgets::widget>
    <x-filament::section heading="Order Pipeline" icon="heroicon-o-funnel">
        <div style="display: flex; flex-direction: column; gap: 8px;">
            @foreach ($this->getStages() as $stage)
                <a href="{{ route('filament.admin.resources.orders.index', ['tableFilters[status][value]' => $stage['key']]) }}"
                   style="text-decoration: none; display: flex; align-items: center; gap: 12px; padding: 10px 14px; border-radius: 10px; background: {{ $stage['bg'] }}; transition: transform 0.15s, box-shadow 0.15s; border-left: 4px solid {{ $stage['color'] }};"
                   onmouseover="this.style.transform='translateX(4px)';this.style.boxShadow='0 2px 8px rgba(0,0,0,0.1)'"
                   onmouseout="this.style.transform='';this.style.boxShadow=''">
                    <span style="font-weight: 700; font-size: 1.25rem; color: {{ $stage['color'] }}; min-width: 36px; text-align: center;">
                        {{ $stage['count'] }}
                    </span>
                    <span style="font-weight: 500; color: #374151; font-size: 0.9rem;">{{ $stage['label'] }}</span>
                    <span style="margin-left: auto; color: {{ $stage['color'] }}; opacity: 0.5;">→</span>
                </a>
            @endforeach
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
