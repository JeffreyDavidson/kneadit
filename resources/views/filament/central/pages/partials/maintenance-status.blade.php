<div class="mb-4">
    @if($active)
        <div class="inline-flex items-center gap-2 rounded-lg bg-red-100 px-4 py-3 text-red-800 dark:bg-red-900/30 dark:text-red-400">
            <x-heroicon-o-exclamation-triangle class="h-6 w-6" />
            <span class="text-lg font-bold">MAINTENANCE</span>
        </div>
    @else
        <div class="inline-flex items-center gap-2 rounded-lg bg-green-100 px-4 py-3 text-green-800 dark:bg-green-900/30 dark:text-green-400">
            <x-heroicon-o-check-circle class="h-6 w-6" />
            <span class="text-lg font-bold">ONLINE</span>
        </div>
    @endif
</div>
