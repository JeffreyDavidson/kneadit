<x-filament-panels::page>
    {{-- ============== HERO STRIP ============== --}}
    <div class="bg-brand-900 border-brand-800/60 mb-6 rounded-xl border p-6">
        <div class="text-brand-300 mb-1 text-[0.65rem] font-semibold tracking-[0.1em] uppercase">
            Storefront Page Content
        </div>
        <h2 class="mb-1 text-[1.1rem] leading-tight font-bold text-white">
            Customize the copy on every storefront page
        </h2>
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
