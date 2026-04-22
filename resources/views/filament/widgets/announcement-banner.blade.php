<div>
    @foreach ($this->getAnnouncements() as $announcement)
        @php
            $variant = match ($announcement['type']) {
                'info' => ['bg' => 'bg-blue-500/15', 'border' => 'border-blue-500/25', 'text' => 'text-blue-500'],
                'warning' => ['bg' => 'bg-honey/15', 'border' => 'border-honey/25', 'text' => 'text-honey'],
                'success' => ['bg' => 'bg-emerald-500/15', 'border' => 'border-emerald-500/25', 'text' => 'text-emerald-500'],
                'maintenance' => ['bg' => 'bg-gray-500/15', 'border' => 'border-gray-500/25', 'text' => 'text-gray-500'],
                default => ['bg' => 'bg-blue-500/15', 'border' => 'border-blue-500/25', 'text' => 'text-blue-500'],
            };
        @endphp
        <div
            x-data="{ dismissed: localStorage.getItem('announcement-dismissed-{{ $announcement['id'] }}') === 'true' }"
            x-show="!dismissed"
            x-cloak
            class="px-4 py-3 mb-3 rounded-lg flex items-start justify-between gap-3 border text-warm-black {{ $variant['bg'] }} {{ $variant['border'] }}"
        >
            <div class="flex-1">
                <div class="font-semibold text-[0.9rem] mb-1 {{ $variant['text'] }}">
                    @if ($announcement['type'] === 'warning') ⚠️
                    @elseif ($announcement['type'] === 'info') ℹ️
                    @elseif ($announcement['type'] === 'success') ✅
                    @elseif ($announcement['type'] === 'maintenance')
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
