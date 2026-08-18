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
            Pick a palette for the platform admin. Affects sidebar, surfaces, tables, and accents. Tenant-facing pages
            and emails are unaffected.
        </p>
    </x-central.card>

    <div class="grid grid-cols-1 gap-5 md:grid-cols-3">
        @foreach ($themes as $key => $theme)
            @php $isActive = $current === $key; @endphp
            <button
                type="button"
                wire:click="selectTheme('{{ $key }}')"
                class="text-left rounded-xl border-2 p-5 transition-all cursor-pointer
                    {{
                        $isActive
                        ? 'border-honey bg-honey/8 shadow-[0_8px_28px_rgba(212,146,12,0.15)]'
                        : 'border-honey/12 bg-warm-black hover:border-honey/30'
                    }}"
            >
                <div class="mb-4 flex items-center justify-between">
                    <div class="text-[1.1rem] font-bold text-white">{{ $theme['label'] }}</div>
                    @if ($isActive)
                        <div class="bg-honey/15 text-honey border-honey/25 inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-[0.65rem] font-bold tracking-[0.1em] uppercase">
                            <x-heroicon-s-check-circle class="h-3.5 w-3.5" />
                            Active
                        </div>
                    @endif
                </div>

                <div class="mb-4 flex gap-1.5">
                    @foreach ($theme['swatches'] as $swatch)
                        <div
                            class="h-10 flex-1 rounded-md border border-white/5"
                            style="background: {{ $swatch }};"
                        ></div>
                    @endforeach
                </div>

                <div class="text-cinnamon text-[0.8rem] leading-snug">{{ $theme['tagline'] }}</div>

                @if (! $isActive)
                    <div class="text-honey mt-4 inline-flex items-center gap-1 text-[0.8rem] font-semibold">
                        Apply theme
                        <x-heroicon-o-arrow-right class="h-3.5 w-3.5" />
                    </div>
                @endif
            </button>
        @endforeach
    </div>
</x-filament-panels::page>
