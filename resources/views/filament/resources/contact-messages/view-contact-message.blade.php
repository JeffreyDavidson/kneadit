@php
    $message = $record;
    $replies = $message->replies()->with('sentBy')->orderBy('sent_at')->get();
@endphp

<x-filament-panels::page>
    {{-- ============== HEADER ============== --}}
    <div class="bg-brand-900 border-brand-800/60 mb-6 rounded-xl border p-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="min-w-0 flex-1">
                <div class="text-brand-300 mb-1 text-[0.65rem] font-semibold tracking-[0.1em] uppercase">
                    Contact Message
                </div>
                <h2 class="text-[1.35rem] leading-tight font-bold text-white">{{ $message->subject }}</h2>
                <div class="text-brand-400 mt-1 text-[0.85rem]">
                    From <span class="text-brand-200 font-semibold">{{ $message->name }}</span> ·
                    <a
                        href="mailto:{{ $message->email }}"
                        class="text-brand-300 hover:text-brand-200"
                    >{{ $message->email }}</a>
                </div>
            </div>

            <span @class([
                'inline-flex items-center gap-1.5 text-[0.7rem] font-bold uppercase tracking-[0.08em] rounded-full px-2.5 py-1 border',
                'bg-emerald-500/15 border-emerald-500/25 text-emerald-400' => $message->is_read,
                'bg-amber-500/15 border-amber-500/25 text-amber-400' => ! $message->is_read,
            ])>
                <span class="h-1.5 w-1.5 rounded-full bg-current"></span>
                {{ $message->is_read ? 'Read' : 'Unread' }}
            </span>
        </div>
    </div>

    {{-- ============== THREAD ============== --}}
    <div class="space-y-4">
        {{-- Original customer message --}}
        <div class="bg-brand-800 border-brand-700/60 overflow-hidden rounded-xl border">
            <div class="bg-brand-900/40 border-brand-700/40 flex items-center justify-between gap-2 border-b px-5 py-3">
                <div class="flex min-w-0 items-center gap-2">
                    <div class="bg-brand-700 text-brand-200 flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-sm font-bold">
                        {{ \Illuminate\Support\Str::of($message->name)->substr(0, 1)->upper() }}
                    </div>
                    <div class="min-w-0">
                        <div class="truncate text-sm font-semibold text-white">{{ $message->name }}</div>
                        <div class="text-brand-400 truncate text-xs">{{ $message->email }}</div>
                    </div>
                </div>
                <div class="text-brand-400 shrink-0 text-xs">
                    {{ $message->created_at->format('M j, Y \a\t g:i A') }}
                </div>
            </div>
            <div class="text-brand-100 p-5 text-sm leading-relaxed whitespace-pre-wrap">{{ $message->message }}</div>
        </div>

        {{-- Replies --}}
        @foreach ($replies as $reply)
            <div class="bg-brand-800 border-brand-700/60 ml-6 overflow-hidden rounded-xl border">
                <div class="bg-brand-900/40 border-brand-700/40 flex items-center justify-between gap-2 border-b px-5 py-3">
                    <div class="flex min-w-0 items-center gap-2">
                        <div class="bg-brand-300/20 text-brand-300 flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-sm font-bold">
                            {{ \Illuminate\Support\Str::of($reply->sentBy?->name ?? 'You')->substr(0, 1)->upper() }}
                        </div>
                        <div class="min-w-0">
                            <div class="truncate text-sm font-semibold text-white">
                                {{ $reply->sentBy?->name ?? 'Staff' }}
                                <span class="text-brand-300 font-normal">replied</span>
                            </div>
                            <div class="text-brand-400 truncate text-xs">{{ $reply->subject }}</div>
                        </div>
                    </div>
                    <div class="text-brand-400 shrink-0 text-xs">
                        {{ $reply->sent_at->format('M j, Y \a\t g:i A') }}
                    </div>
                </div>
                <div class="text-brand-100 p-5 text-sm leading-relaxed whitespace-pre-wrap">{{ $reply->body }}</div>
            </div>
        @endforeach

        @if ($replies->isEmpty())
            <div class="text-brand-400 px-1 text-sm italic">
                No replies yet. Use the Reply button above to send the first response.
            </div>
        @endif
    </div>
</x-filament-panels::page>
