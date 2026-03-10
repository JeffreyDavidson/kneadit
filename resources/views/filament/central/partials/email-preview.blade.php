<div style="background: #f5f0e8; padding: 2rem; border-radius: 0.5rem;">
    <div style="max-width: 600px; margin: 0 auto; background: #faf0d6; border-radius: 0.5rem; box-shadow: 0 2px 8px rgba(0,0,0,0.15); overflow: hidden;">
        {{-- Email header --}}
        <div style="background: #1c1410; padding: 1rem 1.5rem; color: #d4920c; font-size: 0.85rem;">
            <div style="font-weight: 600;">KneadIt Platform</div>
            <div style="color: #8b6844; font-size: 0.75rem;">noreply@getkneadit.app</div>
        </div>

        {{-- Subject --}}
        <div style="padding: 1rem 1.5rem; border-bottom: 1px solid #e8dcc8;">
            <div style="color: #666; font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.05em;">Subject</div>
            <div style="color: #1c1410; font-size: 1.1rem; font-weight: 600;">{{ $campaign->subject }}</div>
        </div>

        {{-- Segment badge --}}
        <div style="padding: 0.75rem 1.5rem; border-bottom: 1px solid #e8dcc8;">
            <span style="background: #d4920c; color: #1c1410; font-size: 0.7rem; font-weight: 600; padding: 0.2rem 0.6rem; border-radius: 9999px; text-transform: uppercase;">
                {{ $campaign->target_segment }}
            </span>
        </div>

        {{-- Body --}}
        <div style="padding: 1.5rem; color: #2a1f18; font-size: 0.95rem; line-height: 1.6;">
            {!! nl2br(e($campaign->body)) !!}
        </div>
    </div>
</div>
