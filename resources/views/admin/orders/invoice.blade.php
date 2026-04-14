<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice - {{ $order->order_number }}</title>
<link rel="stylesheet" href="{{ asset('css/invoice.css') }}">
</head>
<body>
    <!-- Print Button (only visible on screen) -->
    <div class="print-actions no-print">
        <button class="print-button" onclick="window.print()">
            🖨️ Print Invoice
        </button>
    </div>

    <div class="invoice-container">
        <!-- Header -->
        <div class="invoice-header">
            <div class="store-info">
                <h1>{{ $settings->storeName }}</h1>
                <p>{{ $settings->storeAddress ?? 'Address not configured' }}</p>
                <p>{{ $settings->storePhone ?? 'Phone not configured' }}</p>
                <p>{{ $settings->storeEmail ?? 'Email not configured' }}</p>
                <p>{{ $settings->storeWebsite ?? url('/') }}</p>
            </div>
            <div class="invoice-title">
                <h2>INVOICE</h2>
                <p><strong>{{ $order->order_number }}</strong></p>
                <p>{{ $order->created_at->format('F j, Y') }}</p>
            </div>
        </div>

        <!-- Customer and Order Info -->
        <div class="invoice-meta">
            <div class="customer-info">
                <div class="section-title">Bill To</div>
                <div class="info-item">
                    <span class="info-label">Name:</span>
                    <span class="info-value">{{ $order->customer->name ?? 'N/A' }}</span>
                </div>
                @if ($order->customer && $order->customer->email)
                    <div class="info-item">
                        <span class="info-label">Email:</span>
                        <span class="info-value">{{ $order->customer->email }}</span>
                    </div>
                @endif
                @if ($order->delivery_address)
                    <div class="info-item">
                        <span class="info-label">Delivery:</span>
                        <span class="info-value">{{ $order->delivery_address }}</span>
                    </div>
                @endif
            </div>

            <div class="order-info">
                <div class="section-title">Order Details</div>
                <div class="info-item">
                    <span class="info-label">Status:</span>
                    <span class="status-badge status-{{ $order->status->value }}">{{ $order->status->getLabel() }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Order Date:</span>
                    <span class="info-value">{{ $order->created_at->format('M j, Y g:i A') }}</span>
                </div>
                @if ($order->delivery_date)
                    <div class="info-item">
                        <span class="info-label">Requested:</span>
                        <span class="info-value">
                            {{ \Carbon\Carbon::parse($order->delivery_date)->format('M j, Y') }}
                            @if ($order->delivery_time)
                                at {{ \Carbon\Carbon::parse($order->delivery_time)->format('g:i A') }}
                            @endif
                        </span>
                    </div>
                @endif
                <div class="info-item">
                    <span class="info-label">Payment:</span>
                    <span class="info-value">{{ ucfirst($order->payment_method?->value ?? 'N/A') }}</span>
                </div>
            </div>
        </div>

        <!-- Order Items -->
        <div class="order-items">
            <div class="section-title">Order Items</div>
            <table class="items-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th class="text-right">Qty</th>
                        <th class="text-right">Unit Price</th>
                        <th class="text-right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($order->orderItems as $item)
                        <tr>
                            <td>
                                <div class="font-semibold">{{ $item->product ? $item->product->name : 'Product Not Found' }}</div>
                                @if ($item->product && $item->product->description)
                                    <div style="color: #6B7280; font-size: 0.875rem;">
                                        {{ Str::limit($item->product->description, 60) }}
                                    </div>
                                @endif
                            </td>
                            <td class="text-right">{{ $item->quantity }}</td>
                            <td class="text-right">@money($item->unit_price)</td>
                            <td class="text-right font-semibold">@money($item->total_price)</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Totals -->
        <div class="totals-section">
            <table class="totals-table">
                <tr>
                    <td><strong>Subtotal:</strong></td>
                    <td class="text-right">@money($order->subtotal)</td>
                </tr>
                @if ($order->delivery_fee > 0)
                    <tr>
                        <td><strong>Delivery Fee:</strong></td>
                        <td class="text-right">@money($order->delivery_fee)</td>
                    </tr>
                @endif
                @if ($order->discount_amount > 0)
                    <tr style="color: #059669;">
                        <td><strong>Discount:</strong></td>
                        <td class="text-right">-${{ number_format($order->discount_amount, 2) }}</td>
                    </tr>
                @endif
                @if ($order->gift_card_amount > 0)
                    <tr style="color: #059669;">
                        <td><strong>Gift Card:</strong></td>
                        <td class="text-right">-${{ number_format($order->gift_card_amount, 2) }}</td>
                    </tr>
                @endif
                <tr>
                    <td><strong>TOTAL:</strong></td>
                    <td class="text-right"><strong>@money($order->total)</strong></td>
                </tr>
            </table>
        </div>

        @if ($order->notes)
            <div style="margin-top: 2rem; padding: 1.5rem; background: #f8fafc; border-radius: 8px; border-left: 4px solid #3B82F6;">
                <div class="section-title">Notes</div>
                <p style="color: #374151; white-space: pre-line;">{{ $order->notes }}</p>
            </div>
        @endif

        <!-- Footer -->
        <div class="invoice-footer">
            <p><strong>Thank you for your business!</strong></p>
            <p>This invoice was generated on {{ now()->format('F j, Y \a\t g:i A') }}</p>
            <p style="margin-top: 1rem; font-size: 0.75rem; color: #9CA3AF;">
                For questions about this invoice, please contact {{ $settings->storeEmail ?? 'Email not configured' }} or {{ $settings->storePhone ?? 'Phone not configured' }}
            </p>
        </div>
    </div>

    <script>
        // Auto-focus print dialog when page loads (optional)
        // window.addEventListener('load', function() {
        //     setTimeout(() => {
        //         window.print();
        //     }, 500);
        // });
    </script>
</body>
</html>