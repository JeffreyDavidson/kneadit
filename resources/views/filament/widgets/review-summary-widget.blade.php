<x-filament-widgets::widget>
    <x-filament::section heading="Customer Reviews" icon="heroicon-o-star">
        @php
            $avg = $this->getAverageRating();
            $total = $this->getTotalReviews();
            $recent = $this->getRecentReview();
            $dist = $this->getRatingDistribution();
        @endphp

        <div class="flex items-center gap-4 mb-4">
            <div class="text-center">
                <div class="text-3xl font-bold text-brand-900">{{ $avg ?: '—' }}</div>
                <div class="text-golden text-[1.1rem] tracking-[2px]">
                    @for ($i = 1; $i <= 5; $i++)
                        {{ $i <= round($avg) ? '★' : '☆' }}
                    @endfor
                </div>
                <div class="text-xs text-brand-700">{{ $total }} review{{ $total !== 1 ? 's' : '' }}</div>
            </div>
            <div class="flex-1">
                @foreach ($dist as $stars => $data)
                    <div class="flex items-center gap-1.5 mb-1">
                        <span class="text-[0.7rem] text-brand-700 w-3 text-right">{{ $stars }}</span>
                        <div class="flex-1 bg-brand-50 rounded-full h-2 overflow-hidden">
                            <div class="h-full bg-golden rounded-full" style="width: {{ $data['percentage'] }}%;"></div>
                        </div>
                        <span class="text-[0.65rem] text-brand-600 w-6">{{ $data['count'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        @if ($recent)
            <div class="p-3 bg-brand-50 rounded-lg border-l-[3px] border-brand-300">
                <div class="text-xs text-brand-700 mb-1">
                    {{ $recent->customer_name }} — {{ str_repeat('★', $recent->rating) }}
                </div>
                <div class="text-[0.8rem] text-brand-900 italic">
                    "{{ \Illuminate\Support\Str::limit($recent->comment, 120) }}"
                </div>
            </div>
        @else
            <div class="text-brand-600 italic text-[0.8rem]">No reviews yet</div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
