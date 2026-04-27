@php
    $open = $this->getOpenInquiriesCount();
    $pending = $this->getPendingQuotesCount();
    $value = $this->getTotalPipelineValue();
    $latest = $this->getLatestInquiry();
@endphp

<x-admin.dashboard.preview-card heading="Catering Pipeline" icon="📝">
    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px;">
        <x-admin.dashboard.stat-row label="Open" :value="$open" />
        <x-admin.dashboard.stat-row label="Quoted" :value="$pending" />
        <div class="pw-stat">
            <span class="pw-stat-label">Pipeline</span>
            <span style="font-size: 1.1rem; font-weight: 700; color: var(--pw-card-text);">${{ number_format($value, 0) }}</span>
        </div>
    </div>

    @unless ($this->isSize('sm'))
        <div style="margin-top: 12px; padding: 8px 10px; background: var(--pw-card-grad-start); border-radius: 6px;">
            <div style="font-size: 0.55rem; color: var(--pw-card-text-muted); text-transform: uppercase; margin-bottom: 4px;">Latest Inquiry</div>
            @if ($latest)
                <div style="font-weight: 600; color: var(--pw-card-text); font-size: 0.8rem;">{{ $latest->customer_name }}</div>
                <div style="font-size: 0.65rem; color: var(--pw-card-text-muted);">
                    {{ $latest->event_type }} — {{ $latest->event_date?->format('M j, Y') ?? 'No date' }}
                    — {{ $latest->guest_count ?? '?' }} guests
                </div>
            @else
                <div style="color: var(--pw-card-text-muted); font-style: italic; font-size: 0.7rem;">No catering inquiries yet</div>
            @endif
        </div>
    @endunless
</x-admin.dashboard.preview-card>
