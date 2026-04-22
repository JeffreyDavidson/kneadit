@php
    $messages = $getRecord()->messages()->oldest()->get();
@endphp

@if ($messages->isEmpty())
    <p class="text-sm text-gray-500 italic">No messages yet. Use the "Send Message" button above to start a conversation.</p>
@else
    <div class="space-y-3 max-h-96 overflow-y-auto">
        @foreach ($messages as $msg)
            <div class="flex {{ $msg->sender_type->isBaker() ? 'justify-end' : 'justify-start' }}">
                <div class="max-w-md rounded-lg px-4 py-3 {{ $msg->sender_type->isBaker() ? 'bg-amber-900 text-white' : 'bg-amber-50 text-gray-900 border border-amber-200' }}">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="text-xs font-semibold {{ $msg->sender_type->isBaker() ? 'text-amber-200' : 'text-amber-700' }}">
                            {{ $msg->sender_name }}
                            @if ($msg->sender_type->isBaker())
                                <span class="ml-1 px-1.5 py-0.5 rounded text-xs {{ $msg->sender_type->isBaker() ? 'bg-amber-700' : 'bg-amber-200' }}">Baker</span>
                            @endif
                        </span>
                    </div>
                    <p class="text-sm whitespace-pre-wrap">{{ $msg->message }}</p>
                    <p class="text-xs mt-1 {{ $msg->sender_type->isBaker() ? 'text-amber-300' : 'text-amber-500' }}">
                        {{ $msg->created_at->format('M j, g:i A') }}
                        @if (!$msg->sender_type->isBaker() && !$msg->is_read)
                            <span class="ml-2 inline-block w-2 h-2 rounded-full bg-blue-500" title="Unread"></span>
                        @endif
                    </p>
                </div>
            </div>
        @endforeach
    </div>
@endif
