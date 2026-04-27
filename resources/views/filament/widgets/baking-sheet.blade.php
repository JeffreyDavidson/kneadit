@php
    $rows = $this->getRows();
    $totalUnits = array_sum(array_column($rows, 'quantity'));
@endphp

<x-admin.dashboard.preview-card heading="Daily Baking Sheet" icon="🧁">
    <div style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 6px;">
        <span class="pw-stat-label">Today + confirmed ahead</span>
        <span style="font-size: 0.65rem; color: var(--pw-card-text-muted);">{{ count($rows) }} item{{ count($rows) === 1 ? '' : 's' }} · {{ $totalUnits }} unit{{ $totalUnits === 1 ? '' : 's' }}</span>
    </div>

    @forelse ($rows as $row)
        <x-admin.dashboard.list-row :label="$row['name']" :value="$row['quantity'].' to bake'" />
    @empty
        <div style="text-align: center; padding: 12px 0; color: var(--pw-card-text-muted); font-size: 0.75rem;">
            🎉 Nothing to bake!
        </div>
    @endforelse
</x-admin.dashboard.preview-card>
