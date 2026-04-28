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
    <div class="mb-6 bg-brand-900 border border-brand-800/60 rounded-xl p-6 flex flex-col md:flex-row md:items-center gap-5">
        <div class="flex-1 min-w-0">
            <div class="text-brand-300 text-[0.65rem] uppercase tracking-[0.1em] font-semibold mb-1">Catering Inquiry</div>
            <h2 class="text-white text-[1.35rem] font-bold leading-tight truncate">{{ $inquiry->customer_name }}</h2>
            <div class="text-brand-400 text-[0.85rem] mt-1">
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

        <div class="flex items-center gap-2 flex-wrap">
            <span class="inline-flex items-center gap-1.5 bg-brand-800 border border-brand-700 text-brand-200 text-[0.7rem] font-bold uppercase tracking-[0.08em] rounded-full px-2.5 py-1">
                <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                {{ $status->getLabel() }}
            </span>
            @if ($depositChip)
                <span class="inline-flex items-center gap-1.5 {{ $depositChip['bg'] }} border {{ $depositChip['border'] }} {{ $depositChip['text'] }} text-[0.7rem] font-bold uppercase tracking-[0.08em] rounded-full px-2.5 py-1">
                    {{ $depositChip['label'] }}
                </span>
            @endif
        </div>

        <div class="text-right shrink-0">
            <div class="text-brand-300 text-[0.65rem] uppercase tracking-[0.1em] font-semibold mb-0.5">Quoted</div>
            <div class="text-white text-[1.5rem] font-bold leading-none tabular-nums">{{ $inquiry->quoted_amount?->formatted() ?? '—' }}</div>
        </div>
    </div>

    <div class="space-y-6">
        {{-- ============== CUSTOMER ============== --}}
        <div class="bg-brand-900 border border-brand-800/60 rounded-xl p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="text-brand-300 text-[0.65rem] uppercase tracking-[0.1em] font-semibold">Customer</div>
                {{ $this->editCustomerAction }}
            </div>
            <dl class="divide-y divide-brand-700/40">
                <div class="flex items-start justify-between gap-4 py-2.5 first:pt-0 last:pb-0">
                    <dt class="text-brand-400 text-[0.8rem] shrink-0 pt-0.5">Name</dt>
                    <dd class="text-white text-[0.85rem] font-semibold text-right truncate">{{ $inquiry->customer_name }}</dd>
                </div>
                @if ($inquiry->customer_email)
                    <div class="flex items-start justify-between gap-4 py-2.5 last:pb-0">
                        <dt class="text-brand-400 text-[0.8rem] shrink-0 pt-0.5">Email</dt>
                        <dd class="text-white text-[0.85rem] font-semibold text-right truncate">{{ $inquiry->customer_email }}</dd>
                    </div>
                @endif
                @if ($inquiry->customer_phone)
                    <div class="flex items-start justify-between gap-4 py-2.5 last:pb-0">
                        <dt class="text-brand-400 text-[0.8rem] shrink-0 pt-0.5">Phone</dt>
                        <dd class="text-white text-[0.85rem] font-semibold text-right">{{ $inquiry->customer_phone }}</dd>
                    </div>
                @endif
            </dl>
        </div>

        {{-- ============== EVENT DETAILS ============== --}}
        <div class="bg-brand-900 border border-brand-800/60 rounded-xl p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="text-brand-300 text-[0.65rem] uppercase tracking-[0.1em] font-semibold">Event details</div>
                {{ $this->editEventDetailsAction }}
            </div>
            <dl class="divide-y divide-brand-700/40">
                <div class="flex items-start justify-between gap-4 py-2.5 first:pt-0 last:pb-0">
                    <dt class="text-brand-400 text-[0.8rem] shrink-0 pt-0.5">Type</dt>
                    <dd class="text-white text-[0.85rem] font-semibold text-right">{{ $inquiry->event_type }}</dd>
                </div>
                <div class="flex items-start justify-between gap-4 py-2.5 last:pb-0">
                    <dt class="text-brand-400 text-[0.8rem] shrink-0 pt-0.5">Date</dt>
                    <dd class="text-white text-[0.85rem] font-semibold text-right">{{ $eventDate?->format('M j, Y') ?? '—' }}</dd>
                </div>
                <div class="flex items-start justify-between gap-4 py-2.5 last:pb-0">
                    <dt class="text-brand-400 text-[0.8rem] shrink-0 pt-0.5">Guests</dt>
                    <dd class="text-white text-[0.85rem] font-semibold text-right tabular-nums">{{ number_format($inquiry->guest_count) }}</dd>
                </div>
                @if ($inquiry->budget)
                    <div class="flex items-start justify-between gap-4 py-2.5 last:pb-0">
                        <dt class="text-brand-400 text-[0.8rem] shrink-0 pt-0.5">Budget</dt>
                        <dd class="text-white text-[0.85rem] font-semibold text-right tabular-nums">{{ $inquiry->budget->formatted() }}</dd>
                    </div>
                @endif
                @if ($inquiry->venue_address)
                    <div class="flex items-start justify-between gap-4 py-2.5 last:pb-0">
                        <dt class="text-brand-400 text-[0.8rem] shrink-0 pt-0.5">Venue</dt>
                        <dd class="text-white text-[0.85rem] font-semibold text-right whitespace-pre-wrap max-w-md">{{ $inquiry->venue_address }}</dd>
                    </div>
                @endif
                @if ($inquiry->dietary_requirements)
                    <div class="flex items-start justify-between gap-4 py-2.5 last:pb-0">
                        <dt class="text-brand-400 text-[0.8rem] shrink-0 pt-0.5">Dietary</dt>
                        <dd class="text-white text-[0.85rem] font-semibold text-right whitespace-pre-wrap max-w-md">{{ $inquiry->dietary_requirements }}</dd>
                    </div>
                @endif
                @if ($inquiry->details)
                    <div class="flex items-start justify-between gap-4 py-2.5 last:pb-0">
                        <dt class="text-brand-400 text-[0.8rem] shrink-0 pt-0.5">Details</dt>
                        <dd class="text-white text-[0.85rem] font-semibold text-right whitespace-pre-wrap max-w-md">{{ $inquiry->details }}</dd>
                    </div>
                @endif
            </dl>
        </div>

        {{-- ============== QUOTE ============== --}}
        <div class="bg-brand-900 border border-brand-800/60 rounded-xl p-6">
            <div class="flex items-center justify-between mb-4 gap-3 flex-wrap">
                <div class="text-brand-300 text-[0.65rem] uppercase tracking-[0.1em] font-semibold">Quote</div>
                <div class="flex items-center gap-2 flex-wrap">
                    {{ $this->manageQuoteItemsAction }}
                    {{ $this->sendQuoteAction }}
                    {{ $this->resendQuoteAction }}
                </div>
            </div>

            @if ($inquiry->items->isNotEmpty())
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left text-[0.85rem]">
                        <thead class="border-b border-brand-700/40 text-[0.7rem] uppercase tracking-[0.05em] text-brand-400">
                            <tr>
                                <th class="py-2 pr-4 font-semibold">Item</th>
                                <th class="py-2 pr-4 font-semibold text-right">Qty</th>
                                <th class="py-2 pr-4 font-semibold text-right">Unit</th>
                                <th class="py-2 font-semibold text-right">Line total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-brand-700/40">
                            @foreach ($inquiry->items as $item)
                                <tr>
                                    <td class="py-3 pr-4">
                                        <div class="text-white font-semibold">{{ $item->name }}</div>
                                        @if ($item->special_instructions)
                                            <div class="text-brand-400 text-[0.75rem] mt-0.5 italic">"{{ $item->special_instructions }}"</div>
                                        @endif
                                    </td>
                                    <td class="py-3 pr-4 text-right text-white tabular-nums">{{ $item->quantity }}</td>
                                    <td class="py-3 pr-4 text-right text-brand-200 tabular-nums">{{ $item->unit_price->formatted() }}</td>
                                    <td class="py-3 text-right text-white font-semibold tabular-nums">{{ $item->line_total->formatted() }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="border-t border-brand-700/40">
                                <td colspan="3" class="pt-3 text-brand-200 text-[0.95rem] font-bold uppercase tracking-[0.05em] text-right">Total</td>
                                <td class="pt-3 text-right text-white text-[1.25rem] font-bold tabular-nums">{{ $inquiry->quoted_amount?->formatted() ?? '—' }}</td>
                            </tr>
                            <tr>
                                <td colspan="4" class="pt-1 text-right text-brand-400 text-[0.75rem]">
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
                    <div class="text-white text-[1.75rem] font-bold tabular-nums">{{ $inquiry->quoted_amount->formatted() }}</div>
                    <div class="text-brand-400 text-[0.85rem]">
                        @if ($status === CateringInquiryStatus::Inquiry)
                            Not yet sent
                        @else
                            Sent · status: {{ $status->getLabel() }}
                        @endif
                    </div>
                </div>
                <div class="text-brand-400 text-[0.8rem] mt-2">Single-amount quote (added before items existed). Use <span class="text-brand-200 font-semibold">Manage items</span> to break it into line items.</div>
            @else
                <div class="text-brand-400 text-[0.9rem]">No items yet. Use <span class="text-brand-200 font-semibold">Manage items</span> to build the quote.</div>
            @endif
        </div>

        {{-- ============== BOOKING ============== --}}
        <div class="bg-brand-900 border border-brand-800/60 rounded-xl p-6">
            <div class="flex items-center justify-between mb-4 gap-3 flex-wrap">
                <div class="text-brand-300 text-[0.65rem] uppercase tracking-[0.1em] font-semibold">Booking</div>
                @unless ($order)
                    {{ $this->confirmBookingAction }}
                @endunless
            </div>

            @if ($order)
                <a href="{{ \App\Filament\Resources\Orders\OrderResource::getUrl('view', ['record' => $order]) }}"
                   class="flex items-center justify-between gap-4 px-4 py-3 -mx-2 rounded-lg bg-brand-800 border border-brand-700/60 hover:border-brand-300/40 transition-colors group">
                    <div class="min-w-0">
                        <div class="text-brand-300 text-[0.65rem] uppercase tracking-[0.1em] font-semibold mb-0.5">Linked order</div>
                        <div class="text-white text-[0.95rem] font-bold font-mono">{{ $order->order_number }}</div>
                        <div class="text-brand-400 text-[0.8rem] mt-0.5">
                            {{ $order->status->getLabel() }} · {{ $order->payment_status->getLabel() }} · {{ $order->total->formatted() }}
                        </div>
                    </div>
                    <x-heroicon-o-arrow-top-right-on-square class="w-4 h-4 text-brand-400 group-hover:text-brand-200 transition-colors shrink-0" />
                </a>
            @else
                <div class="text-brand-200 text-[0.9rem]">
                    @if ($status === CateringInquiryStatus::Quoted)
                        Awaiting confirmation. Confirming creates an order so the rest of fulfillment (payment, messages, status) is tracked there.
                    @elseif ($status === CateringInquiryStatus::Cancelled)
                        <span class="text-red-400 font-semibold">Cancelled.</span>
                    @else
                        Send a quote first; confirmation becomes available once the customer has been quoted.
                    @endif
                </div>
            @endif
        </div>

        {{-- ============== DEPOSIT ============== --}}
        <div class="bg-brand-900 border border-brand-800/60 rounded-xl p-6">
            <div class="flex items-center justify-between mb-4 gap-3 flex-wrap">
                <div class="text-brand-300 text-[0.65rem] uppercase tracking-[0.1em] font-semibold">Deposit</div>
                {{ $this->markDepositReceivedAction }}
            </div>

            @if ($depositPaid)
                <dl class="divide-y divide-brand-700/40">
                    <div class="flex items-start justify-between gap-4 py-2.5 first:pt-0 last:pb-0">
                        <dt class="text-brand-400 text-[0.8rem] shrink-0 pt-0.5">Amount</dt>
                        <dd class="text-white text-[0.85rem] font-semibold text-right tabular-nums">{{ $inquiry->deposit_amount?->formatted() ?? '—' }}</dd>
                    </div>
                    <div class="flex items-start justify-between gap-4 py-2.5 last:pb-0">
                        <dt class="text-brand-400 text-[0.8rem] shrink-0 pt-0.5">Received</dt>
                        <dd class="text-white text-[0.85rem] font-semibold text-right">{{ $inquiry->deposit_paid_at->format('M j, Y') }}</dd>
                    </div>
                    @if ($inquiry->deposit_reference)
                        <div class="flex items-start justify-between gap-4 py-2.5 last:pb-0">
                            <dt class="text-brand-400 text-[0.8rem] shrink-0 pt-0.5">Reference</dt>
                            <dd class="text-white text-[0.85rem] font-semibold text-right">{{ $inquiry->deposit_reference }}</dd>
                        </div>
                    @endif
                </dl>
                @if ($order)
                    <div class="text-brand-400 text-[0.8rem] mt-3 pt-3 border-t border-brand-700/40">
                        Balance is tracked on the linked order.
                    </div>
                @endif
            @else
                <div class="text-brand-200 text-[0.9rem]">
                    Not received.
                    @if ($suggestedDeposit !== null)
                        Suggested deposit: <span class="text-white font-semibold tabular-nums">${{ number_format($suggestedDeposit, 2) }}</span>
                        <span class="text-brand-400">({{ $depositPercent }}% of quote)</span>
                    @endif
                </div>
            @endif
        </div>

        {{-- ============== INTERNAL NOTES ============== --}}
        <div class="bg-brand-900 border border-brand-800/60 rounded-xl p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="text-brand-300 text-[0.65rem] uppercase tracking-[0.1em] font-semibold">Internal notes</div>
                {{ $this->editNotesAction }}
            </div>

            @if (filled($inquiry->notes))
                <pre class="text-brand-200 text-[0.85rem] leading-relaxed whitespace-pre-wrap font-sans m-0">{{ $inquiry->notes }}</pre>
            @else
                <div class="text-brand-400 text-[0.85rem]">No notes yet.</div>
            @endif
        </div>
    </div>
</x-filament-panels::page>
