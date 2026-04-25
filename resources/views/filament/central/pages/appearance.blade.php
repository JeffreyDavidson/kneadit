<x-filament-panels::page>
    @php
        $themes = [
            'honey' => [
                'label' => 'Honey',
                'tagline' => 'Warm browns + gold accent',
                'swatches' => ['#0c0a09', '#1c1410', '#2a1f18', '#5c4333', '#d4920c'],
            ],
            'slate' => [
                'label' => 'Slate',
                'tagline' => 'Cool slate gray + gold accent',
                'swatches' => ['#0f172a', '#1e293b', '#334155', '#64748b', '#d4920c'],
            ],
            'nord' => [
                'label' => 'Nord',
                'tagline' => 'Polar Night + Frost cyan',
                'swatches' => ['#2e3440', '#3b4252', '#434c5e', '#4c566a', '#88c0d0'],
            ],
        ];
    @endphp

    <x-central.card class="mb-6">
        <x-central.eyebrow class="mb-2">Central Panel Theme</x-central.eyebrow>
        <p class="text-cinnamon text-[0.85rem] leading-relaxed">
            Pick a palette for the platform admin. Affects sidebar, surfaces, tables, and accents.
            Tenant-facing pages and emails are unaffected.
        </p>
    </x-central.card>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        @foreach ($themes as $key => $theme)
            @php $isActive = $current === $key; @endphp
            <button type="button" wire:click="selectTheme('{{ $key }}')"
                class="text-left rounded-xl border-2 p-5 transition-all cursor-pointer
                    {{ $isActive
                        ? 'border-honey bg-honey/8 shadow-[0_8px_28px_rgba(212,146,12,0.15)]'
                        : 'border-honey/12 bg-warm-black hover:border-honey/30' }}">
                <div class="flex items-center justify-between mb-4">
                    <div class="text-white text-[1.1rem] font-bold">{{ $theme['label'] }}</div>
                    @if ($isActive)
                        <div class="inline-flex items-center gap-1.5 bg-honey/15 text-honey text-[0.65rem] font-bold uppercase tracking-[0.1em] border border-honey/25 rounded-full px-2.5 py-1">
                            <x-heroicon-s-check-circle class="w-3.5 h-3.5" />
                            Active
                        </div>
                    @endif
                </div>

                <div class="flex gap-1.5 mb-4">
                    @foreach ($theme['swatches'] as $swatch)
                        <div class="flex-1 h-10 rounded-md border border-white/5"
                            style="background: {{ $swatch }};"></div>
                    @endforeach
                </div>

                <div class="text-cinnamon text-[0.8rem] leading-snug">{{ $theme['tagline'] }}</div>

                @if (! $isActive)
                    <div class="mt-4 inline-flex items-center gap-1 text-honey text-[0.8rem] font-semibold">
                        Apply theme
                        <x-heroicon-o-arrow-right class="w-3.5 h-3.5" />
                    </div>
                @endif
            </button>
        @endforeach
    </div>
</x-filament-panels::page>
