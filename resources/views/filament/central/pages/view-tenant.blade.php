<x-filament-panels::page>
    {{ $this->infolist }}

    @php $stats = $this->getTenantStats(); @endphp

    <div style="margin-top: 1.5rem;">
        <h3 style="color: #d4920c; font-size: 1.1rem; font-weight: 600; margin-bottom: 1rem;">Tenant Database Stats</h3>
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem;">
            <x-central.stat-card label="Products">{{ number_format($stats['products']) }}</x-central.stat-card>
            <x-central.stat-card label="Orders">{{ number_format($stats['orders']) }}</x-central.stat-card>
            <x-central.stat-card label="Revenue">@money($stats['revenue'] / 100)</x-central.stat-card>
            <x-central.stat-card label="Customers">{{ number_format($stats['customers']) }}</x-central.stat-card>
            <x-central.stat-card label="Reviews">{{ number_format($stats['reviews']) }}</x-central.stat-card>
            <x-central.stat-card label="Last Order">{{ $stats['last_order'] ? \Carbon\Carbon::parse($stats['last_order'])->diffForHumans() : 'Never' }}</x-central.stat-card>
        </div>
    </div>
</x-filament-panels::page>
