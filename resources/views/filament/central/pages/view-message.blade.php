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
        <div style="background: #1c1410; border: 1px solid rgba(212,146,12,0.12); border-radius: 12px; padding: 1.5rem; margin-top: 1.5rem;">
            <div style="color: white; font-weight: 700; font-size: 1rem; margin-bottom: 1rem;">Reply</div>
            <form wire:submit="sendReply">
                <textarea
                    wire:model="replyBody"
                    rows="4"
                    placeholder="Type your reply..."
                    style="width: 100%; background: #2a1f18; border: 1px solid rgba(212,146,12,0.12); border-radius: 8px; padding: 0.75rem; color: #faf0d6; font-size: 0.9rem; resize: vertical; box-sizing: border-box; font-family: inherit; outline: none;"
                    onfocus="this.style.borderColor='#d4920c'"
                    onblur="this.style.borderColor='rgba(212,146,12,0.12)'"
                ></textarea>
                @error('replyBody')
                    <p style="color: #ef4444; font-size: 0.8rem; margin: 0.25rem 0 0 0;">{{ $message }}</p>
                @enderror
                <div style="margin-top: 0.75rem; text-align: right;">
                    <button type="submit" style="background: #d4920c; color: #1c1410; border: none; padding: 0.6rem 1.5rem; border-radius: 8px; font-weight: 700; cursor: pointer; font-size: 0.9rem;">
                        Send Reply
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-filament-panels::page>
