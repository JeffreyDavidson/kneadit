<x-filament-widgets::widget>
    <x-filament::section heading="Catering Pipeline" icon="heroicon-o-building-storefront">
        @php
            $open = $this->getOpenInquiriesCount();
            $pending = $this->getPendingQuotesCount();
            $value = $this->getTotalPipelineValue();
            $latest = $this->getLatestInquiry();
        @endphp

        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 12px;">
            <div style="background: #fdf8f2; border-radius: 8px; padding: 12px; text-align: center;">
                <div style="font-size: 1.5rem; font-weight: 700; color: #3d2314;">{{ $open }}</div>
                <div style="font-size: 0.7rem; color: #6b4c3b;">Open</div>
            </div>
            <div style="background: #fdf8f2; border-radius: 8px; padding: 12px; text-align: center;">
                <div style="font-size: 1.5rem; font-weight: 700; color: #e8b04a;">{{ $pending }}</div>
                <div style="font-size: 0.7rem; color: #6b4c3b;">Quoted</div>
            </div>
            <div style="background: #fdf8f2; border-radius: 8px; padding: 12px; text-align: center;">
                <div style="font-size: 1.1rem; font-weight: 700; color: #3d2314;">${{ number_format($value, 0) }}</div>
                <div style="font-size: 0.7rem; color: #6b4c3b;">Pipeline</div>
            </div>
        </div>

        <div style="margin-top: 16px; padding: 12px; background: #fdf8f2; border-radius: 8px;">
            <div style="font-size: 0.75rem; color: #6b4c3b; margin-bottom: 4px;">Latest Inquiry</div>
            @if($latest)
                <div style="font-weight: 600; color: #3d2314;">{{ $latest->customer_name }}</div>
                <div style="font-size: 0.75rem; color: #8b6844;">
                    {{ $latest->event_type }} — {{ $latest->event_date?->format('M j, Y') ?? 'No date' }}
                    — {{ $latest->guest_count ?? '?' }} guests
                </div>
            @else
                <div style="color: #8b6844; font-style: italic;">No catering inquiries yet</div>
            @endif
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
