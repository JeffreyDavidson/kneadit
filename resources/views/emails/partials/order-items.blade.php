<div class="order-items">
    @if (!empty($heading))
    <h4 style="margin-bottom: 10px; color: #8b4513;">{{ $heading }}</h4>
    @endif
    @foreach ($orderItems as $item)
        <div class="order-item">
            <div>
                <div class="item-name">{{ $itemPrefix ?? '' }}{{ $item->product->name }}</div>
                <div class="item-details">
                    Quantity: {{ $item->quantity }}
                    @if ($item->special_instructions)
                        <br><em>{{ ($showInstructionsLabel ?? false) ? 'Special instructions: ' : '' }}{{ $item->special_instructions }}</em>
                    @endif
                </div>
            </div>
            <div class="item-price">@money($item->total_price)</div>
        </div>
    @endforeach
</div>