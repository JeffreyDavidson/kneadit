<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <title>Deliveries — {{ $settings->storeName }}</title>
<link rel="stylesheet" href="{{ asset('css/driver.css') }}">
</head>
<body>

<div class="header">
    <div class="header-top">
        <span class="store-name">🚗 {{ $settings->storeName }}</span>
        <a href="/admin" class="admin-link">← Admin</a>
    </div>
    <div class="header-meta">
        <span>{{ now()->format('l, M j') }}</span>
        <span>{{ $orders->count() }} {{ Str::plural('delivery', $orders->count()) }}</span>
    </div>
</div>

@if (session('success'))
    <div class="flash flash-success">✅ {{ session('success') }}</div>
@endif

<div class="pull-hint">Pull down to refresh</div>

<div class="orders-list">
    @forelse ($orders as $order)
        <div class="order-card">
            <div class="order-header">
                <div>
                    <div class="customer-name">{{ $order->customer->name ?? 'Unknown' }}</div>
                    <div class="order-number">{{ $order->order_number }}</div>
                </div>
                <span class="badge badge-{{ $order->status }}">{{ $order->status }}</span>
            </div>

            @if ($order->delivery_address)
                <div class="order-detail">
                    <span class="icon">📍</span>
                    <a href="https://maps.google.com/?q={{ urlencode($order->delivery_address) }}" target="_blank" class="maps-link">
                        {{ $order->delivery_address }}
                    </a>
                </div>
            @endif

            @if ($order->delivery_time)
                <div class="order-detail">
                    <span class="icon">🕐</span>
                    <span>{{ \Carbon\Carbon::parse($order->delivery_time)->format('g:i A') }}</span>
                </div>
            @endif

            @if ($order->orderItems->count())
                <div class="items-list">
                    {{ $order->orderItems->map(fn($i) => $i->quantity . '× ' . ($i->product->name ?? 'Item'))->join(', ') }}
                </div>
            @endif

            @if ($order->notes)
                <div class="order-detail">
                    <span class="icon">📝</span>
                    <span>{{ $order->notes }}</span>
                </div>
            @endif

            <div class="order-footer">
                <span class="order-total">@money($order->total)</span>
                <form action="{{ route('driver.delivered', $order) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn-delivered" onclick="return confirm('Mark as delivered?')">
                        ✅ Mark Delivered
                    </button>
                </form>
            </div>
        </div>
    @empty
        <div class="empty-state">
            <div class="emoji">🍞</div>
            <h2>No deliveries scheduled for today</h2>
            <p>Check back later or pull down to refresh</p>
        </div>
    @endforelse
</div>

</body>
</html>
