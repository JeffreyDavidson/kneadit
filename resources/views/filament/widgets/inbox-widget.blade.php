<div>
@php
    $count = $this->getUnreadCount();
@endphp

@if ($count > 0)
    <div class="rounded-xl border p-4" style="background-color: #2a1f18; border-color: #d4920c;">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-full" style="background-color: #d4920c;">
                    <x-heroicon-o-envelope class="h-5 w-5" style="color: #1c1410;" />
                </div>
                <div>
                    <p class="text-sm font-semibold" style="color: #e8b04a;">
                        You have {{ $count }} unread {{ Str::plural('message', $count) }} from KneadIt
                    </p>
                    <p class="text-xs" style="color: #f5d88e;">Check your inbox for important updates</p>
                </div>
            </div>
            <a href="{{ $this->getMessagesUrl() }}" class="rounded-lg px-3 py-1.5 text-xs font-medium" style="background-color: #d4920c; color: #1c1410;">
                View Messages
            </a>
        </div>
    </div>
@endif
</div>
