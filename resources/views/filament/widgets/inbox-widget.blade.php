<div>
@php $count = $this->getUnreadCount(); @endphp

@if ($count > 0)
    <div class="rounded-xl border border-honey bg-espresso p-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-honey">
                    <x-heroicon-o-envelope class="w-5 h-5 text-warm-black" />
                </div>
                <div>
                    <p class="text-sm font-semibold text-golden">
                        You have {{ $count }} unread {{ Str::plural('message', $count) }} from KneadIt
                    </p>
                    <p class="text-xs text-butter">Check your inbox for important updates</p>
                </div>
            </div>
            <a href="{{ $this->getMessagesUrl() }}" class="rounded-lg px-3 py-1.5 text-xs font-medium bg-honey text-warm-black">
                View Messages
            </a>
        </div>
    </div>
@endif
</div>
