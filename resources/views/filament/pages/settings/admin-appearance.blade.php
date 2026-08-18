<x-filament-panels::page>
    @php
        // Swatches mirror the actual tenant palette emitted by
        // PanelThemes::tenantHoney / tenantSlate / tenantNord — pick the
        // shades that read most distinctly from each other so the cards
        // visually communicate the theme at a glance.
        $themes = [
            'honey' => [
                'label' => 'Honey',
                'tagline' => 'Warm browns + gold accent — the KneadIt default.',
                'swatches' => ['#3d2314', '#6b4c3b', '#a08060', '#d4a574', '#f5e6d0'],
            ],
            'slate' => [
                'label' => 'Slate',
                'tagline' => 'Cool slate gray + gold accent.',
                'swatches' => ['#1e293b', '#475569', '#94a3b8', '#d4920c', '#f1f5f9'],
            ],
            'nord' => [
                'label' => 'Nord',
                'tagline' => 'Polar Night surfaces + Frost cyan accent.',
                'swatches' => ['#2e3440', '#434c5e', '#5b6478', '#88c0d0', '#eceff4'],
            ],
        ];
    @endphp

    <div class="rounded-xl border border-brand-200 bg-brand-50 px-5 py-4 mb-6">
        <p class="text-sm leading-relaxed" style="color: var(--brand-700);">
            Pick a palette for your bakery's admin panel. Affects the sidebar, surfaces, tables, and accents.
            Your storefront and emails are unaffected — those have their own theme settings.
        </p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        @foreach ($themes as $key => $theme)
            @php $isActive = $current === $key; @endphp
            <button type="button" wire:click="selectTheme('{{ $key }}')"
                @class([
                    'text-left rounded-xl border-2 p-5 transition-all cursor-pointer bg-white',
                    'border-brand-300 shadow-[0_8px_28px_rgba(212,165,116,0.18)]' => $isActive,
                    'border-brand-100 hover:border-brand-300/40' => ! $isActive,
                ])>
                <div class="flex items-center justify-between mb-4">
                    <div class="text-[1.1rem] font-bold" style="color: var(--brand-900);">{{ $theme['label'] }}</div>
                    @if ($isActive)
                        <div class="inline-flex items-center gap-1.5 text-[0.65rem] font-bold uppercase tracking-[0.1em] rounded-full px-2.5 py-1"
                             style="background: rgba(212,165,116,0.15); color: var(--brand-700); border: 1px solid rgba(212,165,116,0.3);">
                            <x-heroicon-s-check-circle class="w-3.5 h-3.5" />
                            Active
                        </div>
                    @endif
                </div>

                <div class="flex gap-1.5 mb-4">
                    @foreach ($theme['swatches'] as $swatch)
                        <div class="flex-1 h-10 rounded-md border" style="background: {{ $swatch }}; border-color: rgba(0,0,0,0.06);"></div>
                    @endforeach
                </div>

                <div class="text-[0.8rem] leading-snug" style="color: var(--brand-600);">{{ $theme['tagline'] }}</div>

                @if (! $isActive)
                    <div class="mt-4 inline-flex items-center gap-1 text-[0.8rem] font-semibold" style="color: var(--brand-700);">
                        Apply theme
                        <x-heroicon-o-arrow-right class="w-3.5 h-3.5" />
                    </div>
                @endif
            </button>
        @endforeach
    </div>
</x-filament-panels::page>
