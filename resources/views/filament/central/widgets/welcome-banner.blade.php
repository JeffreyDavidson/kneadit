<x-central.card class="relative overflow-hidden">
    <div class="absolute -top-5 -right-5 w-[120px] h-[120px] rounded-full bg-honey/5"></div>
    <div class="absolute -bottom-6 right-[60px] w-[90px] h-[90px] rounded-full bg-golden/5"></div>

    <div class="relative z-10">
        <div class="flex items-center gap-2 mb-4">
            <x-heroicon-o-building-storefront class="w-6 h-6 text-honey" />
            <div class="text-white text-base font-bold">KneadIt Platform</div>
        </div>
        <div class="grid grid-cols-4 gap-4">
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
