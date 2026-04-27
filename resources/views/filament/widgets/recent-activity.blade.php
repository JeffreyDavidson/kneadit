@php
    $rows = $this->getRows();
    $isLarge = $this->isSize('lg');
@endphp

<x-admin.dashboard.preview-card heading="Recent Activity" icon="🕐">
    <div style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 6px;">
        <span class="pw-stat-label">Latest events</span>
        <a href="{{ $this->getViewAllUrl() }}" style="font-size: 0.65rem; color: var(--pw-card-accent); text-decoration: none;">View all →</a>
    </div>

    @forelse ($rows as $row)
        <x-admin.dashboard.list-row :value="$row['when']">
            <span style="color: var(--pw-card-text);">{{ $row['action'] }}</span>
            @if ($row['user_name'])
                <span style="color: var(--pw-card-text-muted); margin-left: 6px;">{{ $row['user_name'] }}</span>
            @endif
            @if ($isLarge && $row['description'])
                <span style="color: var(--pw-card-text-muted); margin-left: 6px;">· {{ \Illuminate\Support\Str::limit($row['description'], 60) }}</span>
            @endif
        </x-admin.dashboard.list-row>
    @empty
        <div style="text-align: center; padding: 12px 0; color: var(--pw-card-text-muted); font-size: 0.75rem;">
            No activity yet
        </div>
    @endforelse
</x-admin.dashboard.preview-card>
