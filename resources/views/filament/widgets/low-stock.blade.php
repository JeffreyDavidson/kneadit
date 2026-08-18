@php
    $rows = $this->getRows();
    $isMedium = $this->isSize('md');
@endphp

<x-admin.dashboard.preview-card heading="Low Stock Ingredients" icon="heroicon-o-cube">
    <x-slot:actions>
        <a href="{{ $this->getViewAllUrl() }}" class="pw-card-action">View all</a>
    </x-slot:actions>

    <div style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 6px;">
        <span class="pw-stat-label">{{ count($rows) }} item{{ count($rows) === 1 ? '' : 's' }} at risk</span>
    </div>

    @forelse ($rows as $row)
        <x-admin.dashboard.list-row
            :dot-color="$row['status_color']"
            :value="'Reorder '.$row['reorder_qty'].' '.$row['unit']"
        >
            <span style="color: var(--pw-card-text); font-weight: 600;">{{ $row['name'] }}</span>
            <span style="color: var(--pw-card-text-muted); margin-left: 6px;">{{ $row['current_stock'] }} {{ $row['unit'] }}</span>
            @if ($isMedium && $row['supplier'])
                <span style="color: var(--pw-card-text-muted); margin-left: 6px;">· {{ $row['supplier'] }}</span>
            @endif
        </x-admin.dashboard.list-row>
    @empty
        <x-admin.dashboard.empty-state
            icon="heroicon-o-check-circle"
            title="Pantry looks stocked"
            copy="Ingredients below reorder levels will surface here before they become urgent."
        />
    @endforelse
</x-admin.dashboard.preview-card>
