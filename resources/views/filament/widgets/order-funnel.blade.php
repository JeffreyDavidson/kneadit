<x-filament-widgets::widget>
    <x-filament::section heading="Order Pipeline" icon="heroicon-o-funnel">
        <div class="flex flex-col gap-2">
            @foreach ($this->getVisibleStages() as $stage)
                <a href="{{ route('filament.admin.resources.orders.index', ['tableFilters[status][value]' => $stage['key']]) }}"
                   class="no-underline flex items-center gap-3 px-3.5 py-2.5 rounded-[10px] border-l-4 transition-transform transition-shadow duration-150 hover:translate-x-1 hover:shadow-md {{ $stage['bgClass'] }} {{ $stage['borderClass'] }}">
                    <span class="font-bold text-xl min-w-9 text-center {{ $stage['textClass'] }}">
                        {{ $stage['count'] }}
                    </span>
                    <span class="font-medium text-brand-900 text-[0.9rem]">{{ $stage['label'] }}</span>
                    <span class="ml-auto opacity-50 {{ $stage['textClass'] }}">→</span>
                </a>
            @endforeach
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
