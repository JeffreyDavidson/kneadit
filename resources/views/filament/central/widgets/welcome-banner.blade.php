<x-central.card padding="px-5 py-3">
    <div class="flex items-center justify-between gap-4 flex-wrap text-[0.75rem]">
        <div class="flex items-center gap-2 text-cinnamon">
            <x-heroicon-o-building-storefront class="w-4 h-4 text-honey/60" />
            <span class="text-honey/80 font-semibold">KneadIt Platform</span>
            <span class="text-cinnamon/50">·</span>
            <span class="text-parchment">{{ config('kneadit.version') }}</span>
            <span class="text-cinnamon/50">·</span>
            <span class="capitalize">{{ app()->environment() }}</span>
        </div>
        <div class="flex items-center gap-3 text-cinnamon">
            <span><span class="text-cinnamon/60">PHP</span> {{ PHP_VERSION }}</span>
            <span class="text-cinnamon/50">·</span>
            <span><span class="text-cinnamon/60">Laravel</span> {{ app()->version() }}</span>
        </div>
    </div>
</x-central.card>
