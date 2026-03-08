<x-filament-widgets::widget>
    <x-filament::section heading="Quick Actions" icon="heroicon-o-bolt">
        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px;">
            @foreach ($this->getActions() as $action)
                <a href="{{ $action['url'] }}"
                   style="display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 16px 10px; border-radius: 12px; text-decoration: none; background: {{ $action['bg'] }}; transition: transform 0.15s, box-shadow 0.15s; text-align: center;"
                   onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 4px 12px rgba(0,0,0,0.1)'"
                   onmouseout="this.style.transform='';this.style.boxShadow=''">
                    <div style="color: {{ $action['color'] }}; margin-bottom: 6px;">
                        <x-dynamic-component :component="$action['icon']" style="width: 24px; height: 24px;" />
                    </div>
                    <span style="font-size: 0.8rem; font-weight: 600; color: {{ $action['color'] }};">{{ $action['label'] }}</span>
                </a>
            @endforeach
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
