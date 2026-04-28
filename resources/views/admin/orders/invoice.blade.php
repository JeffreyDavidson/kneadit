@use(App\Presenters\OrderItemPresenter)
@use(App\Enums\Orders\DeliveryType)
@php
    $brand = $settings->branding->brandColorPrimary ?? '#d4920c';
    $isDelivery = $order->delivery_type === DeliveryType::Delivery;
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice — {{ $order->order_number }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/invoice.css') }}">
    <style @cspnonce>:root { --brand: {{ $brand }}; }</style>
</head>
<body>
    {{-- Print toolbar (hidden on print) --}}
    <div class="toolbar no-print">
        <button type="button" class="btn-print" onclick="window.print()">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 20 20" fill="currentColor">
                <path d="M5 4v3H4a2 2 0 00-2 2v3a2 2 0 002 2h1v2a2 2 0 002 2h6a2 2 0 002-2v-2h1a2 2 0 002-2V9a2 2 0 00-2-2h-1V4a2 2 0 00-2-2H7a2 2 0 00-2 2zm8 0v3H7V4h6zm0 8v4H7v-4h6z" />
            </svg>
            Print
        </button>
    </div>

    <main class="invoice">
        {{-- ============== HEADER ============== --}}
        <header class="invoice__header">
            <div class="invoice__brand">
                <h1 class="invoice__store">{{ $settings->store->name }}</h1>
                <p class="invoice__line">{{ $settings->store->address ?? '' }}</p>
                @if ($settings->store->phone)
                    <p class="invoice__line">{{ $settings->store->phone }}</p>
                @endif
                @if ($settings->store->email)
                    <p class="invoice__line">{{ $settings->store->email }}</p>
                @endif
                @if ($settings->store->website)
                    <p class="invoice__line">{{ $settings->store->website }}</p>
                @endif
            </div>
            <div class="invoice__meta">
                <div class="invoice__label">Invoice</div>
                <div class="invoice__number">{{ $order->order_number }}</div>
                <div class="invoice__date">{{ $order->created_at->format('F j, Y') }}</div>
            </div>
        </header>

        {{-- ============== PARTIES ============== --}}
        <section class="invoice__parties">
            <div class="party">
                <div class="party__heading">Bill To</div>
                <div class="party__name">{{ $order->customer->name ?? '—' }}</div>
                @if ($order->customer?->email)
                    <div class="party__line">{{ $order->customer->email }}</div>
                @endif
                @if ($order->customer?->phone)
                    <div class="party__line">{{ $order->customer->phone }}</div>
                @endif
                @if ($order->delivery_address)
                    <div class="party__address">{{ $order->delivery_address }}</div>
                @endif
            </div>

            <dl class="details">
                <div class="details__heading">Order Details</div>
                <div class="details__row">
                    <dt>Status</dt>
                    <dd><span class="status status--{{ $order->status->value }}">{{ $order->status->getLabel() }}</span></dd>
                </div>
                <div class="details__row">
                    <dt>Order date</dt>
                    <dd>{{ $order->created_at->format('M j, Y · g:i A') }}</dd>
                </div>
                @if ($order->delivery_date)
                    <div class="details__row">
                        <dt>{{ $isDelivery ? 'Delivery' : 'Pickup' }}</dt>
                        <dd>
                            {{ \Carbon\Carbon::parse($order->delivery_date)->format('M j, Y') }}
                            @if ($order->delivery_time)
                                · {{ \Carbon\Carbon::parse($order->delivery_time)->format('g:i A') }}
                            @endif
                        </dd>
                    </div>
                @endif
                <div class="details__row">
                    <dt>Payment</dt>
                    <dd>{{ $order->payment_method?->getLabel() ?? '—' }}</dd>
                </div>
            </dl>
        </section>

        {{-- ============== ITEMS ============== --}}
        <section class="invoice__items">
            <table class="items">
                <thead>
                    <tr>
                        <th class="items__product">Product</th>
                        <th class="items__qty">Qty</th>
                        <th class="items__price">Unit price</th>
                        <th class="items__total">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($order->orderItems as $item)
                        <tr>
                            <td class="items__product">
                                <div class="items__name">{{ $item->product?->name ?? '— removed —' }}</div>
                                @if ($item->product?->description)
                                    <div class="items__desc">{{ Str::limit($item->product->description, 80) }}</div>
                                @endif
                                @if ($item->special_instructions)
                                    <div class="items__instr">"{{ $item->special_instructions }}"</div>
                                @endif
                            </td>
                            <td class="items__qty">{{ $item->quantity }}</td>
                            <td class="items__price">@money($item->unit_price)</td>
                            <td class="items__total">@money(OrderItemPresenter::for($item)->totalPrice())</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </section>

        {{-- ============== TOTALS ============== --}}
        <section class="invoice__totals">
            <dl class="totals">
                <div class="totals__row">
                    <dt>Subtotal</dt>
                    <dd>@money($order->subtotal)</dd>
                </div>
                @if ($order->delivery_fee->isPositive())
                    <div class="totals__row">
                        <dt>Delivery fee</dt>
                        <dd>@money($order->delivery_fee)</dd>
                    </div>
                @endif
                @if ($order->discount_amount->isPositive())
                    <div class="totals__row totals__row--credit">
                        <dt>Discount</dt>
                        <dd>−@money($order->discount_amount)</dd>
                    </div>
                @endif
                @if ($order->gift_card_amount->isPositive())
                    <div class="totals__row totals__row--credit">
                        <dt>Gift card</dt>
                        <dd>−@money($order->gift_card_amount)</dd>
                    </div>
                @endif
                @if ($order->tip_amount->isPositive())
                    <div class="totals__row">
                        <dt>Tip</dt>
                        <dd>@money($order->tip_amount)</dd>
                    </div>
                @endif
                <div class="totals__row totals__row--total">
                    <dt>Total</dt>
                    <dd>@money($order->total)</dd>
                </div>
            </dl>
        </section>

        {{-- ============== NOTES ============== --}}
        @if ($order->notes)
            <section class="invoice__notes">
                <div class="notes__heading">Notes</div>
                <p class="notes__body">{{ $order->notes }}</p>
            </section>
        @endif

        {{-- ============== FOOTER ============== --}}
        <footer class="invoice__footer">
            <p class="footer__thanks">Thank you for your business!</p>
            <p class="footer__line">Generated {{ now()->format('F j, Y \a\t g:i A') }}</p>
            @if ($settings->store->email || $settings->store->phone)
                <p class="footer__line">
                    Questions? {{ $settings->store->email }}@if ($settings->store->email && $settings->store->phone) · @endif{{ $settings->store->phone }}
                </p>
            @endif
        </footer>
    </main>
</body>
</html>
