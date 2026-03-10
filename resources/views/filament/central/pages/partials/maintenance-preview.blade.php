<div style="background: #1c1410; border: 1px solid rgba(212,146,12,0.12); border-radius: 12px; padding: 2.5rem; text-align: center;">
    <div style="color: #d4920c; font-size: 1.75rem; font-weight: 700; margin-bottom: 1rem;">🍞 KneadIt</div>
    <div style="color: white; font-size: 1.25rem; font-weight: 700; margin-bottom: 1.5rem;">We'll be back soon!</div>
    <div style="color: #faf0d6; max-width: 28rem; margin: 0 auto; line-height: 1.6;">
        {{ $message ?: 'We are currently performing scheduled maintenance. We\'ll be back shortly!' }}
    </div>
    @if($scheduled_end)
        <div style="margin-top: 1rem; color: #8b6844; font-size: 0.85rem;">
            Expected back: {{ \Carbon\Carbon::parse($scheduled_end)->format('M j, Y g:i A') }}
        </div>
    @endif
</div>
