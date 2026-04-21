<x-central.card class="relative overflow-hidden">
    <div style="position: absolute; top: -20px; right: -20px; width: 120px; height: 120px; border-radius: 50%; background: rgba(212,146,12,0.06);"></div>
    <div style="position: absolute; bottom: -25px; right: 60px; width: 90px; height: 90px; border-radius: 50%; background: rgba(232,176,74,0.04);"></div>

    <div style="position: relative; z-index: 1;">
        <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1rem;">
            <x-heroicon-o-building-storefront class="w-6 h-6 text-honey" />
            <div style="color: #ffffff; font-size: 1rem; font-weight: 700;">KneadIt Platform</div>
        </div>
        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem;">
            @foreach ([
                'Version' => '1.0.0',
                'Environment' => app()->environment(),
                'PHP' => PHP_VERSION,
                'Laravel' => app()->version(),
            ] as $label => $value)
                <div>
                    <x-central.eyebrow>{{ $label }}</x-central.eyebrow>
                    <div class="text-white font-bold text-[0.85rem] mt-0.5">{{ $value }}</div>
                </div>
            @endforeach
        </div>
    </div>
</x-central.card>
