@php
    $rows = $this->getRows();
    $hasViewRoute = \Illuminate\Support\Facades\Route::has('filament.admin.resources.activity-logs.index');
@endphp

<div class="col-span-full bg-brand-800 border border-brand-700/60 rounded-xl p-6">
    <div class="flex justify-between items-center mb-4">
        <div class="text-white font-bold text-base">Recent Audit Log</div>
        @if ($hasViewRoute)
            <a href="{{ $this->getViewAllUrl() }}" class="text-brand-300 text-xs no-underline">View all →</a>
        @endif
    </div>

    @forelse ($rows as $row)
        <div @class([
            'flex items-center gap-3 px-3 py-2 rounded-lg mb-1.5',
            'bg-brand-300/5' => $loop->even,
        ])>
            <span class="text-brand-400 text-[0.7rem] whitespace-nowrap min-w-20">{{ $row['when'] }}</span>
            <span class="inline-block px-2 py-0.5 rounded-full text-[0.65rem] font-semibold text-white whitespace-nowrap {{ $row['action_pill_class'] }}">
                {{ $row['action_label'] }}
            </span>
            <span class="text-brand-100 text-[0.8rem] flex-1 truncate">{{ $row['description'] }}</span>
            <span class="text-brand-400 text-[0.7rem] font-mono">{{ $row['ip_address'] ?? '' }}</span>
        </div>
    @empty
        <div class="p-8 text-center">
            <x-heroicon-o-clipboard-document-list class="w-8 h-8 text-brand-400 mx-auto mb-2 block" />
            <div class="text-brand-400 text-[0.8rem]">No activity yet.</div>
        </div>
    @endforelse
</div>
