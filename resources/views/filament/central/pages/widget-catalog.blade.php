<x-filament-panels::page>
    <div class="mb-6 text-sm text-cinnamon">
        Live previews of every tenant widget rendered against the curated <strong>demo</strong> bakery.
        Use this page to review new widgets before bakery owners see them — no impersonation required.
        If a widget is missing or broken, run <code class="px-1.5 py-0.5 rounded bg-espresso text-honey">php artisan tenants:seed-demo --fresh</code>.
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
        @foreach ($this->renderedWidgets as $widget)
            <x-central.card>
                <header class="flex items-baseline justify-between mb-4 pb-3 border-b border-honey/10">
                    <div class="flex items-center gap-2">
                        <span class="text-lg">{{ $widget['icon'] }}</span>
                        <h3 class="text-base font-bold text-parchment">{{ $widget['name'] }}</h3>
                    </div>
                    <code class="text-[0.7rem] text-cinnamon">{{ $widget['key'] }}</code>
                </header>
                <p class="text-xs text-cinnamon mb-4">{{ $widget['description'] }}</p>

                <div class="widget-preview-frame">
                    {{ $widget['html'] }}
                </div>
            </x-central.card>
        @endforeach
    </div>
</x-filament-panels::page>
