<x-filament-widgets::widget>
    {{-- Mirrors central's QuickActions structure 1:1 — padding, eyebrow
         spec, button sizing, h-full flex column. Uses theme-aware brand
         tokens so the card shifts with Honey/Slate/Nord. w-1/2 keeps the
         card at 50% of dashboard width (central runs a 2-col grid where
         QuickActions is columnSpan=1; tenant runs a 3-col grid so we
         claim the full row and constrain inner width). --}}
    <div class="bg-brand-800 border border-brand-700/60 rounded-xl p-6 h-full flex flex-col justify-center w-1/2">
        <div class="text-brand-300 text-[0.65rem] uppercase tracking-[0.1em] font-semibold mb-4">Quick Actions</div>
        <div class="flex flex-wrap gap-2">
            @if (\Illuminate\Support\Facades\Route::has('filament.admin.pages.quick-order'))
                <a href="{{ route('filament.admin.pages.quick-order') }}"
                   class="inline-flex items-center justify-center gap-1.5 rounded-lg font-bold no-underline bg-brand-300 text-brand-900 hover:opacity-90 px-6 py-2.5 text-sm">
                    <x-heroicon-s-plus-circle class="w-3.5 h-3.5" stroke-width="2.5" />
                    New Order
                </a>
            @endif
            @if (\Illuminate\Support\Facades\Route::has('filament.admin.resources.orders.index'))
                <a href="{{ route('filament.admin.resources.orders.index') }}"
                   class="inline-flex items-center justify-center rounded-lg font-bold no-underline bg-brand-800 text-brand-300 border border-brand-700 hover:border-brand-300 px-4 py-2 text-sm">
                    View Orders
                </a>
            @endif
            @if (\Illuminate\Support\Facades\Route::has('filament.admin.resources.contact-messages.index'))
                <a href="{{ route('filament.admin.resources.contact-messages.index') }}"
                   class="inline-flex items-center justify-center rounded-lg font-bold no-underline bg-brand-800 text-brand-300 border border-brand-700 hover:border-brand-300 px-4 py-2 text-sm">
                    Messages
                </a>
            @endif
        </div>
    </div>
</x-filament-widgets::widget>
