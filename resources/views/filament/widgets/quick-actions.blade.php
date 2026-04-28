<div>
    <x-admin.dashboard.preview-card heading="Quick Actions" icon="⚡">
        <div class="grid grid-cols-2 gap-2.5">
            @foreach ($this->getQuickActions() as $action)
                <a href="{{ $action['url'] }}"
                   class="flex flex-col items-center justify-center px-2.5 py-4 rounded-xl no-underline text-center transition-transform transition-shadow duration-150 hover:-translate-y-0.5 hover:shadow-md {{ $action['bgClass'] }}">
                    <div class="mb-1.5 {{ $action['textClass'] }}">
                        <x-dynamic-component :component="'heroicon-' . $action['icon']->value" class="w-6 h-6" />
                    </div>
                    <span class="text-xs font-semibold {{ $action['textClass'] }}">{{ $action['label'] }}</span>
                </a>
            @endforeach
        </div>
    </x-admin.dashboard.preview-card>
</div>
