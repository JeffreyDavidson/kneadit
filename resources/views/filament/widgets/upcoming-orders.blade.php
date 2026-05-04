@php
    $groups = $this->getUpcomingOrders();
    $hasOrderRoute = \Illuminate\Support\Facades\Route::has('filament.admin.resources.orders.view');
@endphp

<x-admin.dashboard.preview-card heading="Upcoming Orders" icon="heroicon-o-calendar-days">
    @forelse ($groups as $date => $group)
        <div @class(['mt-3' => ! $loop->first])>
            <div class="pw-stat" style="margin-bottom: 4px;">
                <span class="pw-stat-label">{{ $group['label'] }}</span>
                <span style="font-size: 0.65rem; color: var(--pw-card-text-muted);">{{ count($group['orders']) }} order{{ count($group['orders']) === 1 ? '' : 's' }}</span>
            </div>
            @foreach ($group['orders'] as $order)
                <x-admin.dashboard.list-row :value="$order['time']">
                    @if ($hasOrderRoute)
                        <a href="{{ route('filament.admin.resources.orders.view', $order['id']) }}" style="color: var(--pw-card-accent); text-decoration: none;">{{ $order['customer'] }}</a>
                    @else
                        <span style="color: var(--pw-card-text);">{{ $order['customer'] }}</span>
                    @endif
                    <span style="color: var(--pw-card-text-muted); margin-left: 6px;">{{ $order['items'] }} item{{ $order['items'] === 1 ? '' : 's' }} · ${{ $order['total'] }}</span>
                </x-admin.dashboard.list-row>
            @endforeach
        </div>
    @empty
        <div style="text-align: center; padding: 12px 0; color: var(--pw-card-text-muted); font-size: 0.75rem;">
            No upcoming orders in the next few days
        </div>
    @endforelse
</x-admin.dashboard.preview-card>
