<x-filament-widgets::widget>
    <x-filament::section heading="Catering Pipeline" icon="heroicon-o-building-storefront">
        @php
            $open = $this->getOpenInquiriesCount();
            $pending = $this->getPendingQuotesCount();
            $value = $this->getTotalPipelineValue();
            $latest = $this->getLatestInquiry();
        @endphp

        <div class="grid grid-cols-3 gap-3">
            <x-admin.stat-cell label="Open">{{ $open }}</x-admin.stat-cell>
            <x-admin.stat-cell label="Quoted" value-class="text-2xl font-bold text-golden">{{ $pending }}</x-admin.stat-cell>
            <x-admin.stat-cell label="Pipeline" value-class="text-[1.1rem] font-bold text-brand-900">${{ number_format($value, 0) }}</x-admin.stat-cell>
        </div>

        {{-- sm: 3 stats only. md: + Latest Inquiry detail panel. --}}
        @unless ($this->isSize('sm'))
            <div class="mt-4 p-3 bg-brand-50 rounded-lg">
                <div class="text-xs text-brand-700 mb-1">Latest Inquiry</div>
                @if ($latest)
                    <div class="font-semibold text-brand-900">{{ $latest->customer_name }}</div>
                    <div class="text-xs text-brand-600">
                        {{ $latest->event_type }} — {{ $latest->event_date?->format('M j, Y') ?? 'No date' }}
                        — {{ $latest->guest_count ?? '?' }} guests
                    </div>
                @else
                    <div class="text-brand-600 italic">No catering inquiries yet</div>
                @endif
            </div>
        @endunless
    </x-filament::section>
</x-filament-widgets::widget>
