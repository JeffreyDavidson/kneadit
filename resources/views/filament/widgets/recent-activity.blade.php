@php
    $rows = $this->getRows();
    $hasViewRoute = \Illuminate\Support\Facades\Route::has('filament.admin.resources.activity-logs.index');
@endphp

<div class="bg-brand-800 border-brand-700/60 col-span-full rounded-xl border p-6">
    <div class="mb-4 flex items-center justify-between">
        <div class="text-base font-bold text-white">Recent Audit Log</div>
        @if ($hasViewRoute)
            <a href="{{ $this->getViewAllUrl() }}" class="pw-card-action">View all</a>
        @endif
    </div>

    @forelse ($rows as $row)
        <div @class([
            'flex items-center gap-3 px-3 py-2 rounded-lg mb-1.5',
            'bg-brand-300/5' => $loop->even,
        ])>
            <span class="text-brand-400 min-w-20 text-[0.7rem] whitespace-nowrap">{{ $row['when'] }}</span>
            <span class="inline-block px-2 py-0.5 rounded-full text-[0.65rem] font-semibold text-white whitespace-nowrap {{ $row['action_pill_class'] }}">
                {{ $row['action_label'] }}
            </span>
            <span class="text-brand-100 flex-1 truncate text-[0.8rem]">{{ $row['description'] }}</span>
            <span class="text-brand-400 font-mono text-[0.7rem]">{{ $row['ip_address'] ?? '' }}</span>
        </div>
    @empty
        <x-admin.dashboard.empty-state
            icon="heroicon-o-clipboard-document-list"
            title="No tracked activity yet"
            copy="Customer, order, and settings changes will appear here once the bakery starts moving."
        />
    @endforelse
</div>
