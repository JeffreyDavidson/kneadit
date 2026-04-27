<x-filament-widgets::widget>
    <div class="bg-gradient-to-br from-brand-600 via-brand-500 to-brand-300 rounded-2xl px-8 pt-7 pb-9 text-white relative overflow-hidden shadow-lg">
        {{-- Decorative elements --}}
        <div class="absolute top-[-20px] right-[-20px] w-[120px] h-[120px] bg-white/10 rounded-full"></div>
        <div class="absolute bottom-[-30px] right-[60px] w-20 h-20 bg-white/5 rounded-full"></div>

        <div class="flex flex-wrap items-center gap-5 relative z-10">
            {{-- Left: Greeting --}}
            <div class="shrink-0">
                <h2 class="text-[1.65rem] font-bold m-0 mb-1 [text-shadow:0_1px_2px_rgba(0,0,0,0.15)]">
                    {{ $this->getGreeting() }}
                </h2>
                <p class="m-0 opacity-85 text-[0.95rem]">{{ $this->getTodayDate() }}</p>
            </div>

            {{-- Right: Quick stats — flex-1 absorbs leftover row width; each card grows to share it. --}}
            <div class="flex gap-4 flex-wrap flex-1 justify-end">
                @foreach ([
                    ['label' => 'Orders Today', 'value' => $this->getOrdersToday()],
                    ['label' => 'Revenue Today', 'value' => $this->getRevenueToday()],
                    ['label' => 'Pending', 'value' => $this->getPendingOrders(), 'highlight' => $this->getPendingOrders() > 0],
                ] as $stat)
                    <div class="bg-white/20 backdrop-blur rounded-xl px-5 py-3 text-center flex-1 min-w-30 max-w-50">
                        <div @class(['text-2xl font-bold', 'text-yellow-300' => $stat['highlight'] ?? false])>{{ $stat['value'] }}</div>
                        <div class="text-xs opacity-85 uppercase tracking-wider">{{ $stat['label'] }}</div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Quick actions — only render buttons whose tenant-panel routes are registered. --}}
        <div class="flex gap-2.5 mt-5 flex-wrap relative z-10">
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
