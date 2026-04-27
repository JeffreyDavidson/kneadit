@php
    $slots = $this->getSlots();
    $hasViewRoute = \Illuminate\Support\Facades\Route::has('filament.admin.resources.orders.view');
    $isLarge = $this->isSize('lg');
@endphp

<x-admin.dashboard.preview-card heading="Today's Orders" icon="📋">
    <div style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 8px;">
        @if (count($slots) > 0)
            <span class="pw-stat-label">{{ count($slots) }} order{{ count($slots) === 1 ? '' : 's' }} · {{ $this->getRevenueToday() }} today</span>
        @else
            <span class="pw-stat-label">No orders today — enjoy the quiet</span>
        @endif
        <a href="{{ $this->getViewAllUrl() }}" style="font-size: 0.65rem; color: var(--pw-card-accent); text-decoration: none;">View all →</a>
    </div>

    @if (count($slots) > 0)
        @if ($isLarge)
            {{-- LG: full list with status, customer, link to order --}}
            @foreach ($slots as $slot)
                <x-admin.dashboard.list-row :dot-color="$slot['dot_color']" :value="$slot['total']">
                    <span style="color: var(--pw-card-text); font-weight: 600;">{{ $slot['time'] }}</span>
                    @if ($hasViewRoute)
                        <a href="{{ route('filament.admin.resources.orders.view', $slot['id']) }}" style="color: var(--pw-card-accent); text-decoration: none; margin-left: 8px;">{{ $slot['order_number'] }}</a>
                    @else
                        <span style="margin-left: 8px;">{{ $slot['order_number'] }}</span>
                    @endif
                    <span style="color: var(--pw-card-text-muted); margin-left: 6px;">{{ $slot['customer'] }}</span>
                </x-admin.dashboard.list-row>
            @endforeach
        @else
            {{-- MD: compact time-slot grid --}}
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(110px, 1fr)); gap: 6px;">
                @foreach ($slots as $slot)
                    @php
                        $tag = $hasViewRoute ? 'a' : 'div';
                        $href = $hasViewRoute ? route('filament.admin.resources.orders.view', $slot['id']) : null;
                    @endphp
                    <{{ $tag }} @if ($href) href="{{ $href }}" @endif style="background: var(--pw-card-grad-start); border: 1px solid var(--pw-card-border-subtle); border-radius: 8px; padding: 6px 8px; text-decoration: none; display: block;">
                        <div style="font-size: 0.6rem; color: var(--pw-card-text-muted);">{{ $slot['time'] }}</div>
                        <div style="font-size: 0.85rem; font-weight: 700; color: var(--pw-card-text);">{{ $slot['total'] }}</div>
                        <div style="font-size: 0.55rem; color: var(--pw-card-text-muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $slot['customer'] }}</div>
                    </{{ $tag }}>
                @endforeach
            </div>
        @endif
    @endif
</x-admin.dashboard.preview-card>
