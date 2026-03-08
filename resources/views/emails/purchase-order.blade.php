<x-mail::message>
# Purchase Order

**From:** {{ $storeName }}
**To:** {{ $supplierName }}
**Date:** {{ now()->format('F j, Y') }}
**Requested Delivery:** {{ \Carbon\Carbon::parse($requestedDate)->format('F j, Y') }}

---

Please supply the following items:

<x-mail::table>
| Item | SKU | Quantity | Unit Price | Total |
|:-----|:----|--------:|-----------:|------:|
@foreach($items as $item)
| {{ $item['name'] }} | {{ $item['sku'] ?? '—' }} | {{ $item['needed'] }} {{ $item['unit'] }} | ${{ number_format($item['unit_price'], 2) }} | ${{ number_format($item['subtotal'], 2) }} |
@endforeach
| | | | **Total:** | **${{ number_format($total, 2) }}** |
</x-mail::table>

Please confirm receipt of this order and estimated delivery date.

Thank you for your continued partnership.

Best regards,
**{{ $storeName }}**
</x-mail::message>
