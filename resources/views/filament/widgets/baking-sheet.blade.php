@php
    $rows = $this->getRows();
    $totalUnits = array_sum(array_column($rows, 'quantity'));
@endphp

<x-admin.dashboard.preview-card heading="Daily Baking Sheet" icon="heroicon-o-cake">
    <div style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 6px;">
        <span class="pw-stat-label">Today + confirmed ahead</span>
        <span style="font-size: 0.65rem; color: var(--pw-card-text-muted);">{{ count($rows) }} item{{ count($rows) === 1 ? '' : 's' }} · {{ $totalUnits }} unit{{ $totalUnits === 1 ? '' : 's' }}</span>
    </div>

    @forelse ($rows as $row)
        <x-admin.dashboard.list-row :label="$row['name']" :value="$row['quantity'].' to bake'" />
    @empty
        <x-admin.dashboard.empty-state
            icon="heroicon-o-clipboard-document-check"
            title="No bake list yet"
            copy="Confirmed orders with prep quantities will collect here for today's production run."
        />
    @endforelse
</x-admin.dashboard.preview-card>
