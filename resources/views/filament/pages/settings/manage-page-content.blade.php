<x-filament-panels::page>
    {{-- ============== HERO STRIP ============== --}}
    <div class="mb-6 bg-brand-900 border border-brand-800/60 rounded-xl p-6">
        <div class="text-brand-300 text-[0.65rem] uppercase tracking-[0.1em] font-semibold mb-1">Storefront Page Content</div>
        <h2 class="text-white text-[1.1rem] font-bold leading-tight mb-1">Customize the copy on every storefront page</h2>
        <p class="text-brand-400 text-sm">
            Edit hero text, descriptions, and CTAs across the menu, catering, gift cards, and more. Use
            <code class="text-brand-200 font-mono text-[0.8rem]">@{{store_name}}</code>
            for your bakery name and
            <code class="text-brand-200 font-mono text-[0.8rem]">@{{lead_time}}</code>
            for the order lead time hours.
        </p>
    </div>

    {{ $this->content }}
</x-filament-panels::page>
