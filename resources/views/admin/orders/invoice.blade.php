<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice - {{ $order->order_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background: white;
        }

        .invoice-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 2rem;
        }

        .invoice-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 3px solid #3B82F6;
        }

        .store-info h1 {
            font-size: 2rem;
            font-weight: bold;
            color: #3B82F6;
            margin-bottom: 0.5rem;
        }

        .store-info p {
            margin-bottom: 0.25rem;
            color: #666;
        }

        .invoice-title {
            text-align: right;
        }

        .invoice-title h2 {
            font-size: 2rem;
            color: #3B82F6;
            margin-bottom: 0.5rem;
        }

        .invoice-meta {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .customer-info, .order-info {
            background: #f8fafc;
            padding: 1.5rem;
            border-radius: 8px;
            border-left: 4px solid #3B82F6;
        }

        .section-title {
            font-weight: bold;
            font-size: 1.1rem;
            color: #374151;
            margin-bottom: 1rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .info-item {
            margin-bottom: 0.5rem;
        }

        .info-label {
            font-weight: 600;
            color: #4B5563;
            display: inline-block;
            width: 120px;
        }

        .info-value {
            color: #111827;
        }

        .order-items {
            margin: 2rem 0;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            overflow: hidden;
        }

        .items-table thead {
            background: #3B82F6;
            color: white;
        }

        .items-table th,
        .items-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }

        .items-table th {
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .items-table tbody tr:hover {
            background: #f9fafb;
        }

        .items-table tbody tr:last-child td {
            border-bottom: none;
        }

        .text-right {
            text-align: right;
        }

        .font-semibold {
            font-weight: 600;
        }

        .totals-section {
            margin-top: 2rem;
            display: flex;
            justify-content: flex-end;
        }

        .totals-table {
            width: 300px;
        }

        .totals-table tr {
            border-bottom: 1px solid #e5e7eb;
        }

        .totals-table tr:last-child {
            border-bottom: 3px solid #3B82F6;
            font-weight: bold;
            font-size: 1.1rem;
        }

        .totals-table td {
            padding: 0.75rem;
        }

        .status-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.875rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-pending {
            background: #FEF3C7;
            color: #D97706;
        }

        .status-confirmed {
            background: #DBEAFE;
            color: #1D4ED8;
        }

        .status-baking {
            background: #FED7AA;
            color: #EA580C;
        }

        .status-ready {
            background: #D1FAE5;
            color: #059669;
        }

        .status-delivered {
            background: #D1FAE5;
            color: #059669;
        }

        .status-cancelled {
            background: #FECACA;
            color: #DC2626;
        }

        .invoice-footer {
            margin-top: 3rem;
            padding-top: 2rem;
            border-top: 2px solid #e5e7eb;
            text-align: center;
            color: #6B7280;
            font-size: 0.875rem;
        }

        /* Print Styles */
        @media print {
            @page {
                margin: 0.5in;
            }

            body {
                -webkit-print-color-adjust: exact;
                color-adjust: exact;
            }

            .invoice-container {
                max-width: none;
                margin: 0;
                padding: 0;
            }

            .no-print {
                display: none !important;
            }

            .items-table th {
                background: #3B82F6 !important;
                color: white !important;
            }

            .status-badge {
                background-color: #f3f4f6 !important;
                color: #374151 !important;
                border: 1px solid #d1d5db !important;
            }
        }

        @media screen {
            .print-actions {
                position: fixed;
                top: 1rem;
                right: 1rem;
                z-index: 1000;
            }

            .print-button {
                background: #3B82F6;
                color: white;
                border: none;
                padding: 0.75rem 1.5rem;
                border-radius: 8px;
                font-weight: 600;
                cursor: pointer;
                box-shadow: 0 4px 6px rgba(59, 130, 246, 0.3);
                transition: all 0.2s;
            }

            .print-button:hover {
                background: #2563EB;
                transform: translateY(-1px);
                box-shadow: 0 6px 8px rgba(59, 130, 246, 0.4);
            }
        }
    </style>
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
                <h1>{{ $storeInfo['name'] }}</h1>
                <p>{{ $storeInfo['address'] }}</p>
                <p>{{ $storeInfo['phone'] }}</p>
                <p>{{ $storeInfo['email'] }}</p>
                <p>{{ $storeInfo['website'] }}</p>
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
                @if($order->customer && $order->customer->email)
                    <div class="info-item">
                        <span class="info-label">Email:</span>
                        <span class="info-value">{{ $order->customer->email }}</span>
                    </div>
                @endif
                @if($order->delivery_address)
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
                    <span class="status-badge status-{{ $order->status }}">{{ ucfirst($order->status) }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Order Date:</span>
                    <span class="info-value">{{ $order->created_at->format('M j, Y g:i A') }}</span>
                </div>
                @if($order->delivery_date)
                    <div class="info-item">
                        <span class="info-label">Requested:</span>
                        <span class="info-value">
                            {{ \Carbon\Carbon::parse($order->delivery_date)->format('M j, Y') }}
                            @if($order->delivery_time)
                                at {{ \Carbon\Carbon::parse($order->delivery_time)->format('g:i A') }}
                            @endif
                        </span>
                    </div>
                @endif
                <div class="info-item">
                    <span class="info-label">Payment:</span>
                    <span class="info-value">{{ ucfirst($order->payment_method ?? 'N/A') }}</span>
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
                    @foreach($order->orderItems as $item)
                        <tr>
                            <td>
                                <div class="font-semibold">{{ $item->product ? $item->product->name : 'Product Not Found' }}</div>
                                @if($item->product && $item->product->description)
                                    <div style="color: #6B7280; font-size: 0.875rem;">
                                        {{ Str::limit($item->product->description, 60) }}
                                    </div>
                                @endif
                            </td>
                            <td class="text-right">{{ $item->quantity }}</td>
                            <td class="text-right">${{ number_format($item->price, 2) }}</td>
                            <td class="text-right font-semibold">${{ number_format($item->price * $item->quantity, 2) }}</td>
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
                    <td class="text-right">${{ number_format($order->subtotal, 2) }}</td>
                </tr>
                @if($order->delivery_fee > 0)
                    <tr>
                        <td><strong>Delivery Fee:</strong></td>
                        <td class="text-right">${{ number_format($order->delivery_fee, 2) }}</td>
                    </tr>
                @endif
                @if($order->discount_amount > 0)
                    <tr style="color: #059669;">
                        <td><strong>Discount:</strong></td>
                        <td class="text-right">-${{ number_format($order->discount_amount, 2) }}</td>
                    </tr>
                @endif
                <tr>
                    <td><strong>TOTAL:</strong></td>
                    <td class="text-right"><strong>${{ number_format($order->total, 2) }}</strong></td>
                </tr>
            </table>
        </div>

        @if($order->notes)
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
                For questions about this invoice, please contact {{ $storeInfo['email'] }} or {{ $storeInfo['phone'] }}
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