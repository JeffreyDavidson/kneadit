@php
    $rows = $this->getRows();
    $hasViewRoute = \Illuminate\Support\Facades\Route::has('filament.admin.resources.orders.view');
    $isLarge = $this->isSize('lg');
@endphp

<x-admin.dashboard.preview-card heading="Recent Orders" icon="🧾">
    <div style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 6px;">
        <span class="pw-stat-label">{{ count($rows) }} latest</span>
        <a href="{{ $this->getViewAllUrl() }}" style="font-size: 0.65rem; color: var(--pw-card-accent); text-decoration: none;">View all →</a>
    </div>

    @forelse ($rows as $row)
        <x-admin.dashboard.list-row :value="$row['total']" :dot-color="$row['dot_color']">
            @if ($hasViewRoute)
                <a href="{{ route('filament.admin.resources.orders.view', $row['id']) }}" style="color: var(--pw-card-accent); text-decoration: none;">{{ $row['order_number'] }}</a>
            @else
                <span style="color: var(--pw-card-text);">{{ $row['order_number'] }}</span>
            @endif
            <span style="color: var(--pw-card-text-muted); margin-left: 6px;">{{ $row['customer'] }}</span>
            @if ($isLarge && $row['when'])
                <span style="color: var(--pw-card-text-muted); margin-left: 6px;">· {{ $row['when'] }}</span>
            @endif
        </x-admin.dashboard.list-row>
    @empty
        <div style="text-align: center; padding: 12px 0; color: var(--pw-card-text-muted); font-size: 0.75rem;">
            No orders yet
        </div>
    @endforelse
</x-admin.dashboard.preview-card>
