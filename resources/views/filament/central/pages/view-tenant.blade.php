<x-filament-panels::page>
    {{ $this->infolist }}

    @php $stats = $this->getTenantStats(); @endphp

    <div style="margin-top: 1.5rem;">
        <h3 style="color: #d4920c; font-size: 1.1rem; font-weight: 600; margin-bottom: 1rem;">Tenant Database Stats</h3>
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem;">
            @foreach ([
                ['label' => 'Products', 'value' => number_format($stats['products'])],
                ['label' => 'Orders', 'value' => number_format($stats['orders'])],
                ['label' => 'Revenue', 'value' => '$' . number_format($stats['revenue'] / 100, 2)],
                ['label' => 'Customers', 'value' => number_format($stats['customers'])],
                ['label' => 'Reviews', 'value' => number_format($stats['reviews'])],
                ['label' => 'Last Order', 'value' => $stats['last_order'] ? \Carbon\Carbon::parse($stats['last_order'])->diffForHumans() : 'Never'],
            ] as $stat)
                <div style="background: #1c1410; border-radius: 0.75rem; padding: 1.25rem; border: 1px solid #2a1f18;">
                    <div style="color: #d4920c; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em;">{{ $stat['label'] }}</div>
                    <div style="color: #faf0d6; font-size: 1.5rem; font-weight: 700; margin-top: 0.25rem;">{{ $stat['value'] }}</div>
                </div>
            @endforeach
        </div>
    </div>
</x-filament-panels::page>
