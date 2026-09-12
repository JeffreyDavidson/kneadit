@props(['title' => null, 'subtitle' => null])

<div {{ $attributes->class(['bg-white border border-brand-200/60 rounded-xl overflow-hidden']) }}>
    @if ($title)
        <div
            data-admin-gradient-header
            class="from-brand-900 to-brand-700 flex items-center justify-between bg-gradient-to-br px-5 py-3.5"
        >
            <div>
                <h3 data-header-title class="m-0 text-[0.95rem] font-semibold text-white">{{ $title }}</h3>
                @if ($subtitle)
                    <p class="mt-1 mb-0 text-xs text-white/70">{{ $subtitle }}</p>
                @endif
            </div>
        </div>
    @endif
    <div class="px-5 py-4">{{ $slot }}</div>
</div>
