@props(['title' => null, 'subtitle' => null])

<div {{ $attributes->class(['bg-white border border-brand-200/60 rounded-xl overflow-hidden']) }}>
    @if ($title)
        <div data-admin-gradient-header class="flex justify-between items-center px-5 py-3.5 bg-gradient-to-br from-brand-900 to-brand-700">
            <div>
                <h3 data-header-title class="text-white text-[0.95rem] font-semibold m-0">{{ $title }}</h3>
                @if ($subtitle)
                    <p class="text-white/70 text-xs mt-1 mb-0">{{ $subtitle }}</p>
                @endif
            </div>
        </div>
    @endif
    <div class="px-5 py-4">
        {{ $slot }}
    </div>
</div>
