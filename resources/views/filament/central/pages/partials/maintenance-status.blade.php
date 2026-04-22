<div class="mb-6">
    @if ($active)
        <div class="inline-flex items-center gap-3 rounded-xl bg-red-500/12 border border-red-500/25 px-6 py-4">
            <x-heroicon-o-exclamation-triangle class="w-7 h-7 text-red-500" />
            <span class="text-red-500 text-[1.25rem] font-bold uppercase tracking-[0.1em]">Maintenance</span>
        </div>
    @else
        <div class="inline-flex items-center gap-3 rounded-xl bg-emerald-500/12 border border-emerald-500/25 px-6 py-4">
            <x-heroicon-o-check-circle class="w-7 h-7 text-emerald-500" />
            <span class="text-emerald-500 text-[1.25rem] font-bold uppercase tracking-[0.1em]">Online</span>
        </div>
    @endif
</div>
