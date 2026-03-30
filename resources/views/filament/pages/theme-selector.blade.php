<x-filament-panels::page>
    @php
        $currentTheme = settings('storefront_theme', 'classic');
        $themes = [
            'classic' => [
                'name' => 'Classic',
                'desc' => 'Warm bakery feel with cream & brown tones, serif headings',
                'bg' => '#fef9ef',
                'text' => '#1c1410',
                'accent' => '#8b6844',
                'heading_font' => "'Playfair Display', serif",
                'body_font' => "'Inter', sans-serif",
                'radius' => '8px',
                'sample_heading' => 'Fresh Sourdough',
                'sample_text' => 'Baked daily with love',
            ],
            'modern' => [
                'name' => 'Modern',
                'desc' => 'Clean white, sans-serif, sharp corners, bold color pops',
                'bg' => '#ffffff',
                'text' => '#111827',
                'accent' => '#d4920c',
                'heading_font' => "'Inter', sans-serif",
                'body_font' => "'Inter', sans-serif",
                'radius' => '2px',
                'sample_heading' => 'Fresh Sourdough',
                'sample_text' => 'Baked daily with love',
            ],
            'rustic' => [
                'name' => 'Rustic',
                'desc' => 'Earthy, handwritten fonts, muted tones, organic shapes',
                'bg' => '#f5f0e8',
                'text' => '#3d3527',
                'accent' => '#6b7c5e',
                'heading_font' => "'Caveat', cursive",
                'body_font' => "'Inter', sans-serif",
                'radius' => '16px',
                'sample_heading' => 'Fresh Sourdough',
                'sample_text' => 'Baked daily with love',
            ],
            'elegant' => [
                'name' => 'Elegant',
                'desc' => 'Luxury black & white with gold accents, thin serifs',
                'bg' => '#ffffff',
                'text' => '#1a1a1a',
                'accent' => '#b8960c',
                'heading_font' => "'Cormorant Garamond', serif",
                'body_font' => "'Inter', sans-serif",
                'radius' => '0px',
                'sample_heading' => 'Fresh Sourdough',
                'sample_text' => 'Baked daily with love',
            ],
        ];
    @endphp

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @foreach ($themes as $key => $theme)
            <div
                class="relative rounded-xl border-2 overflow-hidden transition-all duration-200 cursor-pointer hover:shadow-lg {{ $currentTheme === $key ? 'border-amber-500 ring-2 ring-amber-300 shadow-lg' : 'border-gray-200 dark:border-gray-700' }}"
                wire:click="selectTheme('{{ $key }}')"
            >
                {{-- Active badge --}}
                @if ($currentTheme === $key)
                    <div class="absolute top-3 right-3 z-10 bg-amber-500 text-white text-xs font-bold px-2 py-1 rounded-full flex items-center gap-1">
                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        Active
                    </div>
                @endif

                {{-- Preview area --}}
                <div class="p-6" style="background: {{ $theme['bg'] }};">
                    {{-- Mini nav bar --}}
                    <div class="rounded-full px-3 py-1.5 mb-4 flex items-center gap-2" style="background: {{ $theme['text'] }}; opacity: 0.9;">
                        <span class="text-xs font-medium" style="color: {{ $theme['accent'] }};">Bakery</span>
                        <span class="text-xs" style="color: {{ $theme['bg'] }}; opacity: 0.7;">Menu</span>
                        <span class="text-xs" style="color: {{ $theme['bg'] }}; opacity: 0.7;">Order</span>
                    </div>

                    {{-- Sample content --}}
                    <h3 class="text-2xl mb-1" style="font-family: {{ $theme['heading_font'] }}; color: {{ $theme['text'] }};">
                        {{ $theme['sample_heading'] }}
                    </h3>
                    <p class="text-sm mb-3" style="font-family: {{ $theme['body_font'] }}; color: {{ $theme['text'] }}; opacity: 0.7;">
                        {{ $theme['sample_text'] }}
                    </p>

                    {{-- Sample button --}}
                    <div class="inline-block px-4 py-1.5 text-xs font-semibold text-white" style="background: {{ $theme['accent'] }}; border-radius: {{ $theme['radius'] }};">
                        Order Now
                    </div>
                </div>

                {{-- Info --}}
                <div class="px-6 py-4 bg-white dark:bg-gray-800 border-t border-gray-100 dark:border-gray-700">
                    <h4 class="font-semibold text-gray-900 dark:text-white">{{ $theme['name'] }}</h4>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $theme['desc'] }}</p>
                </div>
            </div>
        @endforeach
    </div>
</x-filament-panels::page>
