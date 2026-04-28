<x-filament-widgets::widget>
    {{-- Mirrors central's QuickActions structure: bg-card surface,
         eyebrow label, button row with mb-4 between them.
         Tailwind v4 utility merging is finicky with x-admin.card and
         x-admin.eyebrow defaults (light-mode), so the surface and
         eyebrow are inlined here with dark-aware brand tokens. --}}
    {{-- Central QuickActions is columnSpan=1 in a 2-col grid = exactly
         50% of dashboard width. Tenant claims the full row (next widget
         doesn't auto-span full like central's StatsOverviewWidget does)
         and constrains the card to w-1/2 so it occupies the same 50%
         proportion at every breakpoint, with empty space to the right. --}}
    <div class="bg-brand-900 border border-brand-800/60 rounded-xl px-5 py-4 w-1/2">
        <div class="text-brand-300 text-[0.7rem] uppercase tracking-[0.05em] font-semibold mb-4">Quick Actions</div>
        <div class="flex flex-wrap gap-2">
            @if (\Illuminate\Support\Facades\Route::has('filament.admin.pages.quick-order'))
                <x-admin.btn variant="ghost" :href="route('filament.admin.pages.quick-order')" icon="">
                    <x-heroicon-s-plus-circle class="w-4 h-4" />
                    New Order
                </x-admin.btn>
            @endif
            @if (\Illuminate\Support\Facades\Route::has('filament.admin.resources.orders.index'))
                <x-admin.btn variant="ghost" :href="route('filament.admin.resources.orders.index')" icon="">
                    <x-heroicon-s-document-text class="w-4 h-4" />
                    View Orders
                </x-admin.btn>
            @endif
            @if (\Illuminate\Support\Facades\Route::has('filament.admin.resources.contact-messages.index'))
                <x-admin.btn variant="ghost" :href="route('filament.admin.resources.contact-messages.index')" icon="">
                    <x-heroicon-s-envelope class="w-4 h-4" />
                    Messages
                </x-admin.btn>
            @endif
        </div>
    </div>
</x-filament-widgets::widget>
