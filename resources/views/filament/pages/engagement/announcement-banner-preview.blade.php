@use(App\Enums\Platform\AnnouncementType)
@php
    $text = $this->announcement_text ?: 'Your announcement message will appear here...';
    $variant = AnnouncementType::tryFrom($this->announcement_type ?? 'info') ?? AnnouncementType::Info;
    $enabled = $this->announcement_enabled;
@endphp

@if (! $enabled)
    <div class="py-4 text-center text-gray-400 italic dark:text-gray-500">Banner is currently disabled</div>
@else
    <div class="relative px-4 py-3 text-center text-sm font-medium rounded-lg border-2 {{ $variant->bgClass() }} {{ $variant->textClass() }} {{ $variant->borderClass() }}">
        <span>{{ $text }}</span>
        <span class="absolute top-1/2 right-3 -translate-y-1/2 text-lg leading-none opacity-50">&times;</span>
    </div>
@endif
