<x-filament-widgets::widget>
    <x-filament::section heading="Customer Reviews" icon="heroicon-o-star">
        @php
            $avg = $this->getAverageRating();
            $total = $this->getTotalReviews();
            $recent = $this->getRecentReview();
            $dist = $this->getRatingDistribution();
        @endphp

        <div style="display: flex; align-items: center; gap: 16px; margin-bottom: 16px;">
            <div style="text-align: center;">
                <div style="font-size: 2rem; font-weight: 700; color: #3d2314;">{{ $avg ?: '—' }}</div>
                <div style="color: #e8b04a; font-size: 1.1rem; letter-spacing: 2px;">
                    @for($i = 1; $i <= 5; $i++)
                        {{ $i <= round($avg) ? '★' : '☆' }}
                    @endfor
                </div>
                <div style="font-size: 0.75rem; color: #6b4c3b;">{{ $total }} review{{ $total !== 1 ? 's' : '' }}</div>
            </div>
            <div style="flex: 1;">
                @foreach($dist as $stars => $data)
                    <div style="display: flex; align-items: center; gap: 6px; margin-bottom: 3px;">
                        <span style="font-size: 0.7rem; color: #6b4c3b; width: 12px; text-align: right;">{{ $stars }}</span>
                        <div style="flex: 1; background: #fdf8f2; border-radius: 999px; height: 8px; overflow: hidden;">
                            <div style="height: 100%; width: {{ $data['percentage'] }}%; background: #e8b04a; border-radius: 999px;"></div>
                        </div>
                        <span style="font-size: 0.65rem; color: #8b6844; width: 24px;">{{ $data['count'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        @if($recent)
            <div style="padding: 12px; background: #fdf8f2; border-radius: 8px; border-left: 3px solid #d4a574;">
                <div style="font-size: 0.75rem; color: #6b4c3b; margin-bottom: 4px;">
                    {{ $recent->customer_name }} — {{ str_repeat('★', $recent->rating) }}
                </div>
                <div style="font-size: 0.8rem; color: #3d2314; font-style: italic;">
                    "{{ \Illuminate\Support\Str::limit($recent->comment, 120) }}"
                </div>
            </div>
        @else
            <div style="color: #8b6844; font-style: italic; font-size: 0.8rem;">No reviews yet</div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
