@php
    use App\Enums\Customers\CateringInquiryStatus;

    $inquiry = $record;
    $status = $inquiry->status;
    $order = $inquiry->order;

    $eventDate = $inquiry->event_date;
    $eventCountdown = $eventDate?->isFuture() ? $eventDate->diffForHumans(['parts' => 1, 'short' => false]) : null;
    $eventPast = $eventDate?->isPast() ?? false;

    $depositPaid = $inquiry->deposit_paid_at !== null;
    $depositPercent = resolve(\App\Services\Settings\TenantSettings::class)->catering->depositPercent;
    $suggestedDeposit = $inquiry->quoted_amount && $depositPercent > 0
        ? round($inquiry->quoted_amount->dollars() * (min(100, $depositPercent) / 100), 2)
        : null;

    $depositChip = match (true) {
        $depositPaid => ['label' => 'Deposit received', 'bg' => 'bg-emerald-500/15', 'border' => 'border-emerald-500/25', 'text' => 'text-emerald-400'],
        in_array($status, [CateringInquiryStatus::Quoted, CateringInquiryStatus::Confirmed], true) => ['label' => 'Deposit pending', 'bg' => 'bg-amber-500/15', 'border' => 'border-amber-500/25', 'text' => 'text-amber-400'],
        default => null,
    };
@endphp

<x-filament-panels::page>
    {{-- ============== HERO STRIP ============== --}}
    <div class="bg-brand-900 border-brand-800/60 mb-6 flex flex-col gap-5 rounded-xl border p-6 md:flex-row md:items-center">
        <div class="min-w-0 flex-1">
            <div class="text-brand-300 mb-1 text-[0.65rem] font-semibold tracking-[0.1em] uppercase">
                Catering Inquiry
            </div>
            <h2 class="truncate text-[1.35rem] leading-tight font-bold text-white">{{ $inquiry->customer_name }}</h2>
            <div class="text-brand-400 mt-1 text-[0.85rem]">
                <span class="text-brand-200 font-semibold">{{ $inquiry->event_type }}</span>
                @if ($eventDate)
                    · {{ $eventDate->format('M j, Y') }}
                    @if ($eventCountdown)
                        <span class="text-brand-400">(in {{ $eventCountdown }})</span>
                    @elseif ($eventPast)
                        <span class="text-brand-400">(past)</span>
                    @endif
                @endif
                · {{ number_format($inquiry->guest_count) }} guests
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <span class="bg-brand-800 border-brand-700 text-brand-200 inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-[0.7rem] font-bold tracking-[0.08em] uppercase">
                <span class="h-1.5 w-1.5 rounded-full bg-current"></span>
                {{ $status->getLabel() }}
            </span>
            @if ($depositChip)
                <span class="inline-flex items-center gap-1.5 {{ $depositChip['bg'] }} border {{ $depositChip['border'] }} {{ $depositChip['text'] }} text-[0.7rem] font-bold uppercase tracking-[0.08em] rounded-full px-2.5 py-1">
                    {{ $depositChip['label'] }}
                </span>
            @endif
        </div>

        <div class="shrink-0 text-right">
            <div class="text-brand-300 mb-0.5 text-[0.65rem] font-semibold tracking-[0.1em] uppercase">Quoted</div>
            <div class="text-[1.5rem] leading-none font-bold text-white tabular-nums">
                {{ $inquiry->quoted_amount?->formatted() ?? '—' }}
            </div>
        </div>
    </div>

    <div class="space-y-6">
        {{-- ============== CUSTOMER ============== --}}
        <div class="bg-brand-900 border-brand-800/60 rounded-xl border p-6">
            <div class="mb-4 flex items-center justify-between">
                <div class="text-brand-300 text-[0.65rem] font-semibold tracking-[0.1em] uppercase">Customer</div>
                {{ $this->editCustomerAction }}
            </div>
            <dl class="divide-brand-700/40 divide-y">
                <div class="flex items-start justify-between gap-4 py-2.5 first:pt-0 last:pb-0">
                    <dt class="text-brand-400 shrink-0 pt-0.5 text-[0.8rem]">Name</dt>
                    <dd class="truncate text-right text-[0.85rem] font-semibold text-white">
                        {{ $inquiry->customer_name }}
                    </dd>
                </div>
                @if ($inquiry->customer_email)
                    <div class="flex items-start justify-between gap-4 py-2.5 last:pb-0">
                        <dt class="text-brand-400 shrink-0 pt-0.5 text-[0.8rem]">Email</dt>
                        <dd class="truncate text-right text-[0.85rem] font-semibold text-white">
                            {{ $inquiry->customer_email }}
                        </dd>
                    </div>
                @endif
                @if ($inquiry->customer_phone)
                    <div class="flex items-start justify-between gap-4 py-2.5 last:pb-0">
                        <dt class="text-brand-400 shrink-0 pt-0.5 text-[0.8rem]">Phone</dt>
                        <dd class="text-right text-[0.85rem] font-semibold text-white">
                            {{ $inquiry->customer_phone }}
                        </dd>
                    </div>
                @endif
            </dl>
        </div>

        {{-- ============== EVENT DETAILS ============== --}}
        <div class="bg-brand-900 border-brand-800/60 rounded-xl border p-6">
            <div class="mb-4 flex items-center justify-between">
                <div class="text-brand-300 text-[0.65rem] font-semibold tracking-[0.1em] uppercase">Event details</div>
                {{ $this->editEventDetailsAction }}
            </div>
            <dl class="divide-brand-700/40 divide-y">
                <div class="flex items-start justify-between gap-4 py-2.5 first:pt-0 last:pb-0">
                    <dt class="text-brand-400 shrink-0 pt-0.5 text-[0.8rem]">Type</dt>
                    <dd class="text-right text-[0.85rem] font-semibold text-white">{{ $inquiry->event_type }}</dd>
                </div>
                <div class="flex items-start justify-between gap-4 py-2.5 last:pb-0">
                    <dt class="text-brand-400 shrink-0 pt-0.5 text-[0.8rem]">Date</dt>
                    <dd class="text-right text-[0.85rem] font-semibold text-white">
                        {{ $eventDate?->format('M j, Y') ?? '—' }}
                    </dd>
                </div>
                <div class="flex items-start justify-between gap-4 py-2.5 last:pb-0">
                    <dt class="text-brand-400 shrink-0 pt-0.5 text-[0.8rem]">Guests</dt>
                    <dd class="text-right text-[0.85rem] font-semibold text-white tabular-nums">
                        {{ number_format($inquiry->guest_count) }}
                    </dd>
                </div>
                @if ($inquiry->budget)
                    <div class="flex items-start justify-between gap-4 py-2.5 last:pb-0">
                        <dt class="text-brand-400 shrink-0 pt-0.5 text-[0.8rem]">Budget</dt>
                        <dd class="text-right text-[0.85rem] font-semibold text-white tabular-nums">
                            {{ $inquiry->budget->formatted() }}
                        </dd>
                    </div>
                @endif
                @if ($inquiry->venue_address)
                    <div class="flex items-start justify-between gap-4 py-2.5 last:pb-0">
                        <dt class="text-brand-400 shrink-0 pt-0.5 text-[0.8rem]">Venue</dt>
                        <dd class="max-w-md text-right text-[0.85rem] font-semibold whitespace-pre-wrap text-white">
                            {{ $inquiry->venue_address }}
                        </dd>
                    </div>
                @endif
                @if ($inquiry->dietary_requirements)
                    <div class="flex items-start justify-between gap-4 py-2.5 last:pb-0">
                        <dt class="text-brand-400 shrink-0 pt-0.5 text-[0.8rem]">Dietary</dt>
                        <dd class="max-w-md text-right text-[0.85rem] font-semibold whitespace-pre-wrap text-white">
                            {{ $inquiry->dietary_requirements }}
                        </dd>
                    </div>
                @endif
                @if ($inquiry->details)
                    <div class="flex items-start justify-between gap-4 py-2.5 last:pb-0">
                        <dt class="text-brand-400 shrink-0 pt-0.5 text-[0.8rem]">Details</dt>
                        <dd class="max-w-md text-right text-[0.85rem] font-semibold whitespace-pre-wrap text-white">
                            {{ $inquiry->details }}
                        </dd>
                    </div>
                @endif
            </dl>
        </div>

        {{-- ============== QUOTE ============== --}}
        <div class="bg-brand-900 border-brand-800/60 rounded-xl border p-6">
            <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                <div class="text-brand-300 text-[0.65rem] font-semibold tracking-[0.1em] uppercase">Quote</div>
                <div class="flex flex-wrap items-center gap-2">
                    @if ($this->manageQuoteItemsAction->isVisible())
                        {{ $this->manageQuoteItemsAction }}
                    @endif
                    @if ($this->sendQuoteAction->isVisible())
                        {{ $this->sendQuoteAction }}
                    @endif
                    @if ($this->resendQuoteAction->isVisible())
                        {{ $this->resendQuoteAction }}
                    @endif
                </div>
            </div>

            @if ($inquiry->items->isNotEmpty())
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
                            @foreach ($inquiry->items as $item)
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
                                    {{ $inquiry->quoted_amount?->formatted() ?? '—' }}
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
            @elseif ($inquiry->quoted_amount)
                <div class="flex items-baseline gap-3">
                    <div class="text-[1.75rem] font-bold text-white tabular-nums">
                        {{ $inquiry->quoted_amount->formatted() }}
                    </div>
                    <div class="text-brand-400 text-[0.85rem]">
                        @if ($status === CateringInquiryStatus::Inquiry)
                            Not yet sent
                        @else
                            Sent · status: {{ $status->getLabel() }}
                        @endif
                    </div>
                </div>
                @if ($this->manageQuoteItemsAction->isVisible())
                    <div class="text-brand-400 mt-2 text-[0.8rem]">
                        Single-amount quote (added before items existed). Use
                        <span class="text-brand-200 font-semibold">Manage items</span> to break it into line items.
                    </div>
                @endif
            @else
                @if ($this->manageQuoteItemsAction->isVisible())
                    <div class="text-brand-400 text-[0.9rem]">
                        No items yet. Use <span class="text-brand-200 font-semibold">Manage items</span> to build the
                        quote.
                    </div>
                @else
                    <div class="text-brand-400 text-[0.9rem]">No items.</div>
                @endif
            @endif
        </div>

        {{-- ============== BOOKING ============== --}}
        <div class="bg-brand-900 border-brand-800/60 rounded-xl border p-6">
            <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                <div class="text-brand-300 text-[0.65rem] font-semibold tracking-[0.1em] uppercase">Booking</div>
                @if ($this->confirmBookingAction->isVisible())
                    {{ $this->confirmBookingAction }}
                @endif
            </div>

            @if ($order)
                <a
                    href="{{ \App\Filament\Resources\Orders\OrderResource::getUrl('view', ['record' => $order]) }}"
                    class="bg-brand-800 border-brand-700/60 hover:border-brand-300/40 group -mx-2 flex items-center justify-between gap-4 rounded-lg border px-4 py-3 transition-colors"
                >
                    <div class="min-w-0">
                        <div class="text-brand-300 mb-0.5 text-[0.65rem] font-semibold tracking-[0.1em] uppercase">
                            Linked order
                        </div>
                        <div class="font-mono text-[0.95rem] font-bold text-white">{{ $order->order_number }}</div>
                        <div class="text-brand-400 mt-0.5 text-[0.8rem]">
                            {{ $order->status->getLabel() }} · {{ $order->payment_status->getLabel() }} · {{ $order->total->formatted() }}
                        </div>
                    </div>
                    <x-heroicon-o-arrow-top-right-on-square class="text-brand-400 group-hover:text-brand-200 h-4 w-4 shrink-0 transition-colors" />
                </a>
            @else
                <div class="text-brand-200 text-[0.9rem]">
                    @if ($status === CateringInquiryStatus::Quoted)
                        Awaiting confirmation. Confirming creates an order so the rest of fulfillment (payment,
                        messages, status) is tracked there.
                    @elseif ($status === CateringInquiryStatus::Cancelled)
                        <span class="font-semibold text-red-400">Cancelled.</span>
                    @else
                        Send a quote first; confirmation becomes available once the customer has been quoted.
                    @endif
                </div>
            @endif
        </div>

        {{-- ============== DEPOSIT ============== --}}
        <div class="bg-brand-900 border-brand-800/60 rounded-xl border p-6">
            <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                <div class="text-brand-300 text-[0.65rem] font-semibold tracking-[0.1em] uppercase">Deposit</div>
                @if ($this->markDepositReceivedAction->isVisible())
                    {{ $this->markDepositReceivedAction }}
                @endif
            </div>

            @if ($depositPaid)
                <dl class="divide-brand-700/40 divide-y">
                    <div class="flex items-start justify-between gap-4 py-2.5 first:pt-0 last:pb-0">
                        <dt class="text-brand-400 shrink-0 pt-0.5 text-[0.8rem]">Amount</dt>
                        <dd class="text-right text-[0.85rem] font-semibold text-white tabular-nums">
                            {{ $inquiry->deposit_amount?->formatted() ?? '—' }}
                        </dd>
                    </div>
                    <div class="flex items-start justify-between gap-4 py-2.5 last:pb-0">
                        <dt class="text-brand-400 shrink-0 pt-0.5 text-[0.8rem]">Received</dt>
                        <dd class="text-right text-[0.85rem] font-semibold text-white">
                            {{ $inquiry->deposit_paid_at->format('M j, Y') }}
                        </dd>
                    </div>
                    @if ($inquiry->deposit_reference)
                        <div class="flex items-start justify-between gap-4 py-2.5 last:pb-0">
                            <dt class="text-brand-400 shrink-0 pt-0.5 text-[0.8rem]">Reference</dt>
                            <dd class="text-right text-[0.85rem] font-semibold text-white">
                                {{ $inquiry->deposit_reference }}
                            </dd>
                        </div>
                    @endif
                </dl>
                @if ($order)
                    <div class="text-brand-400 border-brand-700/40 mt-3 border-t pt-3 text-[0.8rem]">
                        Balance is tracked on the linked order.
                    </div>
                @endif
            @else
                <div class="text-brand-200 text-[0.9rem]">
                    Not received.
                    @if ($suggestedDeposit !== null)
                        Suggested deposit:
                        <span class="font-semibold text-white tabular-nums">${{ number_format($suggestedDeposit, 2) }}</span>
                        <span class="text-brand-400">({{ $depositPercent }}% of quote)</span>
                    @endif
                </div>
            @endif
        </div>

        {{-- ============== INTERNAL NOTES ============== --}}
        <div class="bg-brand-900 border-brand-800/60 rounded-xl border p-6">
            <div class="mb-4 flex items-center justify-between">
                <div class="text-brand-300 text-[0.65rem] font-semibold tracking-[0.1em] uppercase">Internal notes</div>
                {{ $this->editNotesAction }}
            </div>

            @if (filled($inquiry->notes))
                <pre class="text-brand-200 m-0 font-sans text-[0.85rem] leading-relaxed whitespace-pre-wrap">{{ $inquiry->notes }}</pre>
            @else
                <div class="text-brand-400 text-[0.85rem]">No notes yet.</div>
            @endif
        </div>
    </div>
</x-filament-panels::page>
