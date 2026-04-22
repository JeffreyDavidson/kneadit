<x-filament-panels::page>
    <div style="max-width: 800px; margin: 0 auto;">
        {{-- Original message --}}
        @php $isAdmin = $record->sender_type === 'admin'; @endphp
        <div style="display: flex; justify-content: {{ $isAdmin ? 'flex-end' : 'flex-start' }}; margin-bottom: 1.5rem;">
            <div style="max-width: 75%; background: {{ $isAdmin ? '#2a1f18' : '#1c1410' }}; border: 1px solid {{ $isAdmin ? 'rgba(212,146,12,0.3)' : 'rgba(212,146,12,0.12)' }}; border-radius: 12px; padding: 1.5rem; {{ $isAdmin ? 'border-right: 3px solid #d4920c;' : '' }}">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.75rem;">
                    <span style="background: {{ $isAdmin ? '#d4920c' : '#2a1f18' }}; color: {{ $isAdmin ? '#1c1410' : '#e8b04a' }}; padding: 0.2rem 0.6rem; border-radius: 9999px; font-size: 0.65rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.1em;">
                        {{ $isAdmin ? 'Admin' : $record->tenant->name }}
                    </span>
                    <span style="color: #8b6844; font-size: 0.75rem;">{{ $record->created_at->diffForHumans() }}</span>
                </div>
                <div style="color: #faf0d6; line-height: 1.6;">
                    {!! nl2br(e($record->body)) !!}
                </div>
            </div>
        </div>

        {{-- Thread replies --}}
        @foreach ($this->getThread() as $reply)
            @php $isReplyAdmin = $reply->sender_type === 'admin'; @endphp
            <div style="display: flex; justify-content: {{ $isReplyAdmin ? 'flex-end' : 'flex-start' }}; margin-bottom: 1rem;">
                <div style="max-width: 75%; background: {{ $isReplyAdmin ? '#2a1f18' : '#1c1410' }}; border: 1px solid {{ $isReplyAdmin ? 'rgba(212,146,12,0.3)' : 'rgba(212,146,12,0.12)' }}; border-radius: 12px; padding: 1.25rem; {{ $isReplyAdmin ? 'border-right: 3px solid #d4920c;' : '' }}">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.5rem;">
                        <span style="background: {{ $isReplyAdmin ? '#d4920c' : '#2a1f18' }}; color: {{ $isReplyAdmin ? '#1c1410' : '#e8b04a' }}; padding: 0.2rem 0.6rem; border-radius: 9999px; font-size: 0.65rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.1em;">
                            {{ $isReplyAdmin ? 'Admin' : $record->tenant->name }}
                        </span>
                        <span style="color: #8b6844; font-size: 0.75rem;">{{ $reply->created_at->diffForHumans() }}</span>
                    </div>
                    <div style="color: #faf0d6; line-height: 1.6;">
                        {!! nl2br(e($reply->body)) !!}
                    </div>
                </div>
            </div>
        @endforeach

        {{-- Reply form --}}
        <x-central.card title="Reply" class="mt-6">
            <form wire:submit="sendReply">
                <x-central.textarea wire:model="replyBody" rows="4" placeholder="Type your reply..." />
                @error('replyBody')
                    <p class="text-red-500 text-[0.8rem] mt-1">{{ $message }}</p>
                @enderror
                <div class="mt-3 text-right">
                    <x-central.button type="submit">Send Reply</x-central.button>
                </div>
            </form>
        </x-central.card>
    </div>
</x-filament-panels::page>
