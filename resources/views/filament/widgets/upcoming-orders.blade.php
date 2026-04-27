<x-filament-widgets::widget>
    <x-filament::section heading="Upcoming Orders" icon="heroicon-o-calendar-days">
        @php $groups = $this->getUpcomingOrders(); @endphp

        @forelse ($groups as $date => $group)
            <div class="mb-4">
                <div class="font-semibold text-[0.8rem] uppercase tracking-wider text-brand-600 mb-2 pb-1 border-b-2 border-brand-200">
                    {{ $group['label'] }} ({{ count($group['orders']) }})
                </div>
                @php
                    $hasOrderRoute = \Illuminate\Support\Facades\Route::has('filament.admin.resources.orders.view');
                @endphp
                @foreach ($group['orders'] as $order)
                    @php
                        $tag = $hasOrderRoute ? 'a' : 'div';
                        $href = $hasOrderRoute ? route('filament.admin.resources.orders.view', $order['id']) : null;
                    @endphp
                    <{{ $tag }} @if ($href) href="{{ $href }}" @endif
                       class="flex justify-between items-center px-2.5 py-2 rounded-lg no-underline text-inherit mb-1 transition-colors hover:bg-brand-100">
                        <div>
                            <span class="font-semibold text-[0.85rem] text-brand-900">{{ $order['customer'] }}</span>
                            <span class="text-xs text-brand-500 ml-1.5">{{ $order['items'] }} items</span>
                        </div>
                        <div class="text-right">
                            <span class="text-[0.8rem] text-brand-500">{{ $order['time'] }}</span>
                            <span class="text-[0.8rem] font-semibold text-brand-600 ml-2">${{ $order['total'] }}</span>
                        </div>
                    </{{ $tag }}>
                @endforeach
            </div>
        @empty
            <div class="text-center p-6 text-brand-500">
                <p class="m-0">No upcoming orders in the next 3 days</p>
            </div>
        @endforelse
    </x-filament::section>
</x-filament-widgets::widget>
