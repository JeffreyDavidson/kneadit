@use(App\Enums\Platform\AnnouncementType)
@php
    $text = $this->announcement_text ?: 'Your announcement message will appear here...';
    $variant = AnnouncementType::tryFrom($this->announcement_type ?? 'info') ?? AnnouncementType::Info;
    $enabled = $this->announcement_enabled;
@endphp

@if (!$enabled)
    <div class="text-center text-gray-400 dark:text-gray-500 py-4 italic">
        Banner is currently disabled
    </div>
@else
    <div class="relative px-4 py-3 text-center text-sm font-medium rounded-lg border-2 {{ $variant->bgClass() }} {{ $variant->textClass() }} {{ $variant->borderClass() }}">
        <span>{{ $text }}</span>
        <span class="absolute right-3 top-1/2 -translate-y-1/2 opacity-50 text-lg leading-none">&times;</span>
    </div>
@endif
