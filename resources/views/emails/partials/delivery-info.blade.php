@if ($order->delivery_address)
    <div class="delivery-info">
        <div class="info-label">🚚 Delivery Schedule</div>
        <p style="margin: 5px 0;"><strong>Delivery Date:</strong> {{ $order->delivery_date?->format('M j, Y') ?? 'TBD' }}</p>
        <p style="margin: 5px 0;"><strong>Delivery Time:</strong> {{ $order->delivery_time?->format('g:i A') ?? 'TBD' }}</p>
        <p style="margin: 5px 0;"><strong>Address:</strong> {{ $order->delivery_address }}</p>
    </div>
@else
    <div class="delivery-info">
        <div class="info-label">🏪 Pickup Schedule</div>
        <p style="margin: 5px 0;"><strong>Pickup Date:</strong> {{ $order->delivery_date?->format('M j, Y') ?? 'TBD' }}</p>
        <p style="margin: 5px 0;"><strong>Pickup Time:</strong> {{ $order->delivery_time?->format('g:i A') ?? 'TBD' }}</p>
        <p style="margin: 5px 0;"><strong>Location:</strong> {{ $storeAddress ?? '' }}</p>
    </div>
@endif