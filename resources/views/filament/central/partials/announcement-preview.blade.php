@php
    $title = $get('title') ?: 'Announcement title';
    $body = $get('body') ?: 'Your message body will appear here as you type.';
    $typeRaw = $get('type');
    $typeValue = $typeRaw instanceof \BackedEnum ? $typeRaw->value : ($typeRaw ?: 'info');
    $isDismissable = (bool) $get('is_dismissable');

    $palette = match ($typeValue) {
        'warning' => ['bg' => '#fef3c7', 'border' => '#f59e0b', 'text' => '#78350f', 'icon' => 'heroicon-o-exclamation-triangle', 'iconColor' => '#b45309'],
        'success' => ['bg' => '#dcfce7', 'border' => '#10b981', 'text' => '#064e3b', 'icon' => 'heroicon-o-check-circle', 'iconColor' => '#059669'],
        'holiday' => ['bg' => '#fce7f3', 'border' => '#ec4899', 'text' => '#831843', 'icon' => 'heroicon-o-sparkles', 'iconColor' => '#be185d'],
        'maintenance' => ['bg' => '#fee2e2', 'border' => '#ef4444', 'text' => '#7f1d1d', 'icon' => 'heroicon-o-wrench-screwdriver', 'iconColor' => '#dc2626'],
        default => ['bg' => '#fef3c7', 'border' => '#d4920c', 'text' => '#78350f', 'icon' => 'heroicon-o-information-circle', 'iconColor' => '#d4920c'],
    };
@endphp

<div>
    <div class="mb-2 flex items-center justify-between">
        <span class="text-cinnamon text-[0.7rem] font-semibold tracking-[0.08em] uppercase">Live preview</span>
        <span class="text-cinnamon text-[0.7rem]">How bakers will see it</span>
    </div>

    <div style="background-color: {{ $palette['bg'] }}; border-left: 4px solid {{ $palette['border'] }}; border-radius: 8px; padding: 16px 18px; display: flex; align-items: flex-start; gap: 12px;">
        <div style="flex-shrink: 0; color: {{ $palette['iconColor'] }}; margin-top: 2px;">
            <x-dynamic-component :component="$palette['icon']" style="width: 20px; height: 20px" />
        </div>
        <div style="flex: 1; min-width: 0">
            <div style="color: {{ $palette['text'] }}; font-weight: 700; font-size: 14px; margin-bottom: 4px;">
                {{ $title }}
            </div>
            <div style="color: {{ $palette['text'] }}; font-size: 13px; line-height: 1.5; opacity: 0.85;">
                {!! clean($body) !!}
            </div>
        </div>
        @if ($isDismissable)
            <div style="color: {{ $palette['text'] }}; opacity: 0.5; flex-shrink: 0; margin-top: 2px;">
                <x-heroicon-o-x-mark style="width: 16px; height: 16px" />
            </div>
        @endif
    </div>
</div>
