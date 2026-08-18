@php
    $message = $record;
    $replies = $message->replies()->with('sentBy')->orderBy('sent_at')->get();
@endphp

<x-filament-panels::page>
    {{-- ============== HEADER ============== --}}
    <div class="mb-6 bg-brand-900 border border-brand-800/60 rounded-xl p-6">
        <div class="flex items-start justify-between gap-4 flex-wrap">
            <div class="min-w-0 flex-1">
                <div class="text-brand-300 text-[0.65rem] uppercase tracking-[0.1em] font-semibold mb-1">Contact Message</div>
                <h2 class="text-white text-[1.35rem] font-bold leading-tight">{{ $message->subject }}</h2>
                <div class="text-brand-400 text-[0.85rem] mt-1">
                    From <span class="text-brand-200 font-semibold">{{ $message->name }}</span>
                    · <a href="mailto:{{ $message->email }}" class="text-brand-300 hover:text-brand-200">{{ $message->email }}</a>
                </div>
            </div>

            <span @class([
                'inline-flex items-center gap-1.5 text-[0.7rem] font-bold uppercase tracking-[0.08em] rounded-full px-2.5 py-1 border',
                'bg-emerald-500/15 border-emerald-500/25 text-emerald-400' => $message->is_read,
                'bg-amber-500/15 border-amber-500/25 text-amber-400' => ! $message->is_read,
            ])>
                <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                {{ $message->is_read ? 'Read' : 'Unread' }}
            </span>
        </div>
    </div>

    {{-- ============== THREAD ============== --}}
    <div class="space-y-4">
        {{-- Original customer message --}}
        <div class="bg-brand-800 border border-brand-700/60 rounded-xl overflow-hidden">
            <div class="flex items-center justify-between gap-2 px-5 py-3 bg-brand-900/40 border-b border-brand-700/40">
                <div class="flex items-center gap-2 min-w-0">
                    <div class="w-8 h-8 rounded-full bg-brand-700 flex items-center justify-center text-brand-200 text-sm font-bold shrink-0">
                        {{ \Illuminate\Support\Str::of($message->name)->substr(0, 1)->upper() }}
                    </div>
                    <div class="min-w-0">
                        <div class="text-white text-sm font-semibold truncate">{{ $message->name }}</div>
                        <div class="text-brand-400 text-xs truncate">{{ $message->email }}</div>
                    </div>
                </div>
                <div class="text-brand-400 text-xs shrink-0">
                    {{ $message->created_at->format('M j, Y \a\t g:i A') }}
                </div>
            </div>
            <div class="p-5 text-brand-100 text-sm leading-relaxed whitespace-pre-wrap">{{ $message->message }}</div>
        </div>

        {{-- Replies --}}
        @foreach ($replies as $reply)
            <div class="bg-brand-800 border border-brand-700/60 rounded-xl overflow-hidden ml-6">
                <div class="flex items-center justify-between gap-2 px-5 py-3 bg-brand-900/40 border-b border-brand-700/40">
                    <div class="flex items-center gap-2 min-w-0">
                        <div class="w-8 h-8 rounded-full bg-brand-300/20 flex items-center justify-center text-brand-300 text-sm font-bold shrink-0">
                            {{ \Illuminate\Support\Str::of($reply->sentBy?->name ?? 'You')->substr(0, 1)->upper() }}
                        </div>
                        <div class="min-w-0">
                            <div class="text-white text-sm font-semibold truncate">
                                {{ $reply->sentBy?->name ?? 'Staff' }}
                                <span class="text-brand-300 font-normal">replied</span>
                            </div>
                            <div class="text-brand-400 text-xs truncate">{{ $reply->subject }}</div>
                        </div>
                    </div>
                    <div class="text-brand-400 text-xs shrink-0">
                        {{ $reply->sent_at->format('M j, Y \a\t g:i A') }}
                    </div>
                </div>
                <div class="p-5 text-brand-100 text-sm leading-relaxed whitespace-pre-wrap">{{ $reply->body }}</div>
            </div>
        @endforeach

        @if ($replies->isEmpty())
            <div class="text-brand-400 text-sm italic px-1">
                No replies yet. Use the Reply button above to send the first response.
            </div>
        @endif
    </div>
</x-filament-panels::page>
