@use(App\Enums\Platform\AnnouncementType)
<div>
    @foreach ($this->getAnnouncements() as $announcement)
        @php $variant = AnnouncementType::from($announcement['type']); @endphp
        <div
            x-data="{ dismissed: localStorage.getItem('announcement-dismissed-{{ $announcement['id'] }}') === 'true' }"
            x-show="! dismissed"
            x-cloak
            class="px-4 py-3 mb-3 rounded-lg flex items-start justify-between gap-3 border text-warm-black {{ $variant->bgClass() }} {{ $variant->borderClass() }}"
        >
            <div class="flex-1">
                <div class="font-semibold text-[0.9rem] mb-1 {{ $variant->textClass() }}">
                    @if ($variant === AnnouncementType::Warning)
                        <x-filament::icon icon="heroicon-o-exclamation-triangle" class="inline h-4 w-4 align-[-2px]" />
                    @elseif ($variant === AnnouncementType::Info)
                        <x-filament::icon icon="heroicon-o-information-circle" class="inline h-4 w-4 align-[-2px]" />
                    @elseif ($variant === AnnouncementType::Success)
                        <x-filament::icon icon="heroicon-o-check-circle" class="inline h-4 w-4 align-[-2px]" />
                    @endif
                    {{ $announcement['title'] }}
                </div>
                <div class="text-[0.85rem] leading-normal">{!! clean($announcement['body']) !!}</div>
            </div>
            @if ($announcement['is_dismissable'])
                <button
                    x-on:click="dismissed = true; localStorage.setItem('announcement-dismissed-{{ $announcement['id'] }}', 'true')"
                    class="cursor-pointer border-0 bg-transparent px-1 text-[1.2rem] leading-none text-gray-500"
                    title="Dismiss"
                >
                    &times;
                </button>
            @endif
        </div>
    @endforeach
</div>
