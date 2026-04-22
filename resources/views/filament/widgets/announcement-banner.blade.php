@use(App\Enums\Platform\AnnouncementType)
<div>
    @foreach ($this->getAnnouncements() as $announcement)
        @php $variant = AnnouncementType::from($announcement['type']); @endphp
        <div
            x-data="{ dismissed: localStorage.getItem('announcement-dismissed-{{ $announcement['id'] }}') === 'true' }"
            x-show="!dismissed"
            x-cloak
            class="px-4 py-3 mb-3 rounded-lg flex items-start justify-between gap-3 border text-warm-black {{ $variant->bgClass() }} {{ $variant->borderClass() }}"
        >
            <div class="flex-1">
                <div class="font-semibold text-[0.9rem] mb-1 {{ $variant->textClass() }}">
                    @if ($variant === AnnouncementType::Warning) ⚠️
                    @elseif ($variant === AnnouncementType::Info) ℹ️
                    @elseif ($variant === AnnouncementType::Success) ✅
                    @endif
                    {{ $announcement['title'] }}
                </div>
                <div class="text-[0.85rem] leading-normal">
                    {!! clean($announcement['body']) !!}
                </div>
            </div>
            @if ($announcement['is_dismissable'])
                <button
                    x-on:click="dismissed = true; localStorage.setItem('announcement-dismissed-{{ $announcement['id'] }}', 'true')"
                    class="bg-transparent border-0 cursor-pointer text-[1.2rem] text-gray-500 px-1 leading-none"
                    title="Dismiss"
                >&times;</button>
            @endif
        </div>
    @endforeach
</div>
