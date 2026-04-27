<div>
@php $count = $this->getUnreadCount(); @endphp

@if ($count > 0)
    <div class="rounded-xl border border-honey bg-espresso p-4">
        @if ($this->isSize('sm'))
            <a href="{{ $this->getMessagesUrl() }}" class="flex items-center gap-3 no-underline">
                <div class="flex h-9 w-9 items-center justify-center rounded-full bg-honey shrink-0">
                    <x-heroicon-o-envelope class="w-4 h-4 text-warm-black" />
                </div>
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-golden">{{ $count }} unread {{ Str::plural('message', $count) }}</p>
                    <p class="text-xs text-butter">Tap to view</p>
                </div>
            </a>
        @else
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
                <x-central.button :href="$this->getMessagesUrl()" size="xs">
                    View Messages
                </x-central.button>
            </div>
        @endif
    </div>
@endif
</div>
