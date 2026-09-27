@php
    use App\Enums\Customers\CateringInquiryStatus;
@endphp

@props(['items', 'status', 'quotedAmount', 'canManageItems' => false])

@if ($items->isNotEmpty())
    <div class="overflow-x-auto">
        <table class="min-w-full text-left text-[0.85rem]">
            <thead class="border-brand-700/40 text-brand-400 border-b text-[0.7rem] tracking-[0.05em] uppercase">
                <tr>
                    <th class="py-2 pr-4 font-semibold">Item</th>
                    <th class="py-2 pr-4 text-right font-semibold">Qty</th>
                    <th class="py-2 pr-4 text-right font-semibold">Unit</th>
                    <th class="py-2 text-right font-semibold">Line total</th>
                </tr>
            </thead>
            <tbody class="divide-brand-700/40 divide-y">
                @foreach ($items as $item)
                    <tr>
                        <td class="py-3 pr-4">
                            <div class="font-semibold text-white">{{ $item->name }}</div>
                            @if ($item->special_instructions)
                                <div class="text-brand-400 mt-0.5 text-[0.75rem] italic">
                                    "{{ $item->special_instructions }}"
                                </div>
                            @endif
                        </td>
                        <td class="py-3 pr-4 text-right text-white tabular-nums">{{ $item->quantity }}</td>
                        <td class="text-brand-200 py-3 pr-4 text-right tabular-nums">
                            {{ $item->unit_price->formatted() }}
                        </td>
                        <td class="py-3 text-right font-semibold text-white tabular-nums">
                            {{ $item->line_total->formatted() }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="border-brand-700/40 border-t">
                    <td
                        colspan="3"
                        class="text-brand-200 pt-3 text-right text-[0.95rem] font-bold tracking-[0.05em] uppercase"
                    >
                        Total
                    </td>
                    <td class="pt-3 text-right text-[1.25rem] font-bold text-white tabular-nums">
                        {{ $quotedAmount?->formatted() ?? '—' }}
                    </td>
                </tr>
                <tr>
                    <td colspan="4" class="text-brand-400 pt-1 text-right text-[0.75rem]">
                        @if ($status === CateringInquiryStatus::Inquiry)
                            Not yet sent
                        @else
                            Sent · status: {{ $status->getLabel() }}
                        @endif
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
@elseif ($quotedAmount)
    <div class="flex items-baseline gap-3">
        <div class="text-[1.75rem] font-bold text-white tabular-nums">{{ $quotedAmount->formatted() }}</div>
        <div class="text-brand-400 text-[0.85rem]">
            @if ($status === CateringInquiryStatus::Inquiry)
                Not yet sent
            @else
                Sent · status: {{ $status->getLabel() }}
            @endif
        </div>
    </div>
    @if ($canManageItems)
        <div class="text-brand-400 mt-2 text-[0.8rem]">
            Single-amount quote (added before items existed). Use
            <span class="text-brand-200 font-semibold">Manage items</span> to break it into line items.
        </div>
    @endif
@else
    @if ($canManageItems)
        <div class="text-brand-400 text-[0.9rem]">
            No items yet. Use <span class="text-brand-200 font-semibold">Manage items</span> to build the quote.
        </div>
    @else
        <div class="text-brand-400 text-[0.9rem]">No items.</div>
    @endif
@endif
