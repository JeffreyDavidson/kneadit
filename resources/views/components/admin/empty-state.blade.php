@props(['icon' => '', 'title' => 'No data found', 'subtitle' => ''])

<div class="text-center px-5 py-10 text-brand-500">
    @if ($icon)
        <div class="text-[2.5rem] mb-3">
            @if (is_string($icon) && str_starts_with($icon, 'heroicon-'))
                <x-filament::icon :icon="$icon" class="mx-auto h-10 w-10" />
            @else
                {{ $icon }}
            @endif
        </div>
    @endif
    <h3 class="text-[1.1rem] font-semibold text-brand-700 mb-1.5">{{ $title }}</h3>
    @if ($subtitle)
        <p class="text-[0.9rem]">{{ $subtitle }}</p>
    @endif
</div>
