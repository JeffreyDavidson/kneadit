<div>
@php
    $count = $this->getUnreadCount();
@endphp

@if ($count > 0)
    <div class="rounded-xl border p-4" style="background-color: #2a1f18; border-color: #d4920c;">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-full" style="background-color: #d4920c;">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: 20px; height: 20px; color: #1c1410;"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" /></svg>
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
