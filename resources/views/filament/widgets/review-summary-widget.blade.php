@php
    $avg = $this->getAverageRating();
    $total = $this->getTotalReviews();
    $recent = $this->getRecentReview();
    $dist = $this->getRatingDistribution();
@endphp

<x-admin.dashboard.preview-card heading="Customer Reviews" icon="⭐">
    <div style="display: flex; align-items: center; gap: 12px;">
        <div style="text-align: center;">
            <div style="font-size: 1.6rem; font-weight: 700; color: var(--pw-card-text);">{{ $avg ?: '—' }}</div>
            <div style="color: var(--pw-card-accent-light); font-size: 0.85rem; letter-spacing: 2px;">
                @for ($i = 1; $i <= 5; $i++)
                    {{ $i <= round($avg) ? '★' : '☆' }}
                @endfor
            </div>
            <div style="font-size: 0.65rem; color: var(--pw-card-text-muted);">{{ $total }} review{{ $total !== 1 ? 's' : '' }}</div>
        </div>

        @unless ($this->isSize('sm'))
            <div style="flex: 1;">
                @foreach ($dist as $stars => $data)
                    <div style="display: flex; align-items: center; gap: 6px; margin-bottom: 3px;">
                        <span style="font-size: 0.65rem; color: var(--pw-card-text-muted); width: 10px; text-align: right;">{{ $stars }}</span>
                        <div style="flex: 1;" class="pw-bar"><div class="pw-bar-fill" style="width: {{ $data['percentage'] }}%;"></div></div>
                        <span style="font-size: 0.6rem; color: var(--pw-card-text-muted); width: 22px;">{{ $data['count'] }}</span>
                    </div>
                @endforeach
            </div>
        @endunless
    </div>

    @unless ($this->isSize('sm'))
        @if ($recent)
            <div style="margin-top: 12px; padding: 8px 10px; background: var(--pw-card-grad-start); border-left: 3px solid var(--pw-card-accent); border-radius: 6px;">
                <div style="font-size: 0.6rem; color: var(--pw-card-text-muted); margin-bottom: 2px;">
                    {{ $recent->customer_name }} — {{ str_repeat('★', $recent->rating) }}
                </div>
                <div style="font-size: 0.7rem; color: var(--pw-card-text); font-style: italic; line-height: 1.4;">
                    "{{ \Illuminate\Support\Str::limit($recent->comment, 120) }}"
                </div>
            </div>
        @else
            <div style="margin-top: 8px; font-size: 0.7rem; color: var(--pw-card-text-muted); font-style: italic;">No reviews yet</div>
        @endif
    @endunless
</x-admin.dashboard.preview-card>
