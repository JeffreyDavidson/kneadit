<x-filament-panels::page>
    {{ $this->infolist }}

    @php $stats = $this->getTenantStats(); @endphp

    <div class="mt-6">
        <h3 class="text-honey text-[1.1rem] font-semibold mb-4">Tenant Database Stats</h3>
        <div class="grid grid-cols-3 gap-4">
            <x-central.stat-card label="Products">{{ number_format($stats['products']) }}</x-central.stat-card>
            <x-central.stat-card label="Orders">{{ number_format($stats['orders']) }}</x-central.stat-card>
            <x-central.stat-card label="Revenue">@money($stats['revenue'] / 100)</x-central.stat-card>
            <x-central.stat-card label="Customers">{{ number_format($stats['customers']) }}</x-central.stat-card>
            <x-central.stat-card label="Reviews">{{ number_format($stats['reviews']) }}</x-central.stat-card>
            <x-central.stat-card label="Last Order">{{ $stats['last_order'] ? \Carbon\Carbon::parse($stats['last_order'])->diffForHumans() : 'Never' }}</x-central.stat-card>
        </div>
    </div>
</x-filament-panels::page>
