@php
    use App\Enums\Customers\CateringInquiryStatus;
@endphp

<x-filament-panels::page>
    {{-- ============== HERO STRIP ============== --}}
    <div class="bg-brand-900 border-brand-800/60 mb-6 flex flex-col gap-5 rounded-xl border p-6 md:flex-row md:items-center">
        <div class="min-w-0 flex-1">
            <div class="text-brand-300 mb-1 text-[0.65rem] font-semibold tracking-[0.1em] uppercase">
                Catering Inquiry
            </div>
            <h2 class="truncate text-[1.35rem] leading-tight font-bold text-white">
                {{ $viewModel->inquiry->customer_name }}
            </h2>
            <div class="text-brand-400 mt-1 text-[0.85rem]">
                <span class="text-brand-200 font-semibold">{{ $viewModel->inquiry->event_type }}</span>
                @if ($viewModel->eventDate)
                    · {{ $viewModel->eventDate->format('M j, Y') }}
                    @if ($viewModel->eventCountdown)
                        <span class="text-brand-400">(in {{ $viewModel->eventCountdown }})</span>
                    @elseif ($viewModel->eventPast)
                        <span class="text-brand-400">(past)</span>
                    @endif
                @endif
                · {{ number_format($viewModel->inquiry->guest_count) }} guests
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <span class="bg-brand-800 border-brand-700 text-brand-200 inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-[0.7rem] font-bold tracking-[0.08em] uppercase">
                <span class="h-1.5 w-1.5 rounded-full bg-current"></span>
                {{ $viewModel->status->getLabel() }}
            </span>
            @if ($viewModel->depositChip)
                <span class="inline-flex items-center gap-1.5 {{ $viewModel->depositChip['bg'] }} border {{ $viewModel->depositChip['border'] }} {{ $viewModel->depositChip['text'] }} text-[0.7rem] font-bold uppercase tracking-[0.08em] rounded-full px-2.5 py-1">
                    {{ $viewModel->depositChip['label'] }}
                </span>
            @endif
        </div>

        <div class="shrink-0 text-right">
            <div class="text-brand-300 mb-0.5 text-[0.65rem] font-semibold tracking-[0.1em] uppercase">Quoted</div>
            <div class="text-[1.5rem] leading-none font-bold text-white tabular-nums">
                {{ $viewModel->inquiry->quoted_amount?->formatted() ?? '—' }}
            </div>
        </div>
    </div>

    <div class="space-y-6">
        {{-- ============== CUSTOMER ============== --}}
        <x-filament.catering-inquiries.section-card title="Customer">
            <x-slot:actions>{{ $this->editCustomerAction }}</x-slot:actions>

            <dl class="divide-brand-700/40 divide-y">
                <x-filament.catering-inquiries.detail-row label="Name" class="truncate">
                    {{ $viewModel->inquiry->customer_name }}
                </x-filament.catering-inquiries.detail-row>
                @if ($viewModel->inquiry->customer_email)
                    <x-filament.catering-inquiries.detail-row label="Email" class="truncate">
                        {{ $viewModel->inquiry->customer_email }}
                    </x-filament.catering-inquiries.detail-row>
                @endif
                @if ($viewModel->inquiry->customer_phone)
                    <x-filament.catering-inquiries.detail-row label="Phone">
                        {{ $viewModel->inquiry->customer_phone }}
                    </x-filament.catering-inquiries.detail-row>
                @endif
            </dl>
        </x-filament.catering-inquiries.section-card>

        {{-- ============== EVENT DETAILS ============== --}}
        <x-filament.catering-inquiries.section-card title="Event details">
            <x-slot:actions>{{ $this->editEventDetailsAction }}</x-slot:actions>

            <dl class="divide-brand-700/40 divide-y">
                <x-filament.catering-inquiries.detail-row label="Type">
                    {{ $viewModel->inquiry->event_type }}
                </x-filament.catering-inquiries.detail-row>
                <x-filament.catering-inquiries.detail-row label="Date">
                    {{ $viewModel->eventDate?->format('M j, Y') ?? '—' }}
                </x-filament.catering-inquiries.detail-row>
                <x-filament.catering-inquiries.detail-row label="Guests" class="tabular-nums">
                    {{ number_format($viewModel->inquiry->guest_count) }}
                </x-filament.catering-inquiries.detail-row>
                @if ($viewModel->inquiry->budget)
                    <x-filament.catering-inquiries.detail-row label="Budget" class="tabular-nums">
                        {{ $viewModel->inquiry->budget->formatted() }}
                    </x-filament.catering-inquiries.detail-row>
                @endif
                @if ($viewModel->inquiry->venue_address)
                    <x-filament.catering-inquiries.detail-row label="Venue" class="max-w-md whitespace-pre-wrap">
                        {{ $viewModel->inquiry->venue_address }}
                    </x-filament.catering-inquiries.detail-row>
                @endif
                @if ($viewModel->inquiry->dietary_requirements)
                    <x-filament.catering-inquiries.detail-row label="Dietary" class="max-w-md whitespace-pre-wrap">
                        {{ $viewModel->inquiry->dietary_requirements }}
                    </x-filament.catering-inquiries.detail-row>
                @endif
                @if ($viewModel->inquiry->details)
                    <x-filament.catering-inquiries.detail-row label="Details" class="max-w-md whitespace-pre-wrap">
                        {{ $viewModel->inquiry->details }}
                    </x-filament.catering-inquiries.detail-row>
                @endif
            </dl>
        </x-filament.catering-inquiries.section-card>

        {{-- ============== QUOTE ============== --}}
        <x-filament.catering-inquiries.section-card
            title="Quote"
            header-class="mb-4 flex flex-wrap items-center justify-between gap-3"
        >
            <x-slot:actions>
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
            </x-slot:actions>

            <x-filament.catering-inquiries.quote-content
                :items="$viewModel->inquiry->items"
                :status="$viewModel->status"
                :quoted-amount="$viewModel->inquiry->quoted_amount"
                :can-manage-items="$this->manageQuoteItemsAction->isVisible()"
            />
        </x-filament.catering-inquiries.section-card>

        {{-- ============== BOOKING ============== --}}
        <x-filament.catering-inquiries.section-card
            title="Booking"
            header-class="mb-4 flex flex-wrap items-center justify-between gap-3"
        >
            <x-slot:actions>
                @if ($this->confirmBookingAction->isVisible())
                    {{ $this->confirmBookingAction }}
                @endif
            </x-slot:actions>

            @if ($viewModel->order)
                <a
                    href="{{ \App\Filament\Resources\Orders\OrderResource::getUrl('view', ['record' => $viewModel->order]) }}"
                    class="bg-brand-800 border-brand-700/60 hover:border-brand-300/40 group -mx-2 flex items-center justify-between gap-4 rounded-lg border px-4 py-3 transition-colors"
                >
                    <div class="min-w-0">
                        <div class="text-brand-300 mb-0.5 text-[0.65rem] font-semibold tracking-[0.1em] uppercase">
                            Linked order
                        </div>
                        <div class="font-mono text-[0.95rem] font-bold text-white">
                            {{ $viewModel->order->order_number }}
                        </div>
                        <div class="text-brand-400 mt-0.5 text-[0.8rem]">
                            {{ $viewModel->order->status->getLabel() }} · {{ $viewModel->order->payment_status->getLabel() }} · {{ $viewModel->order->total->formatted() }}
                        </div>
                    </div>
                    <x-heroicon-o-arrow-top-right-on-square class="text-brand-400 group-hover:text-brand-200 h-4 w-4 shrink-0 transition-colors" />
                </a>
            @else
                <div class="text-brand-200 text-[0.9rem]">
                    @if ($viewModel->status === CateringInquiryStatus::Quoted)
                        Awaiting confirmation. Confirming creates an order so the rest of fulfillment (payment,
                        messages, status) is tracked there.
                    @elseif ($viewModel->status === CateringInquiryStatus::Cancelled)
                        <span class="font-semibold text-red-400">Cancelled.</span>
                    @else
                        Send a quote first; confirmation becomes available once the customer has been quoted.
                    @endif
                </div>
            @endif
        </x-filament.catering-inquiries.section-card>

        {{-- ============== DEPOSIT ============== --}}
        <x-filament.catering-inquiries.section-card
            title="Deposit"
            header-class="mb-4 flex flex-wrap items-center justify-between gap-3"
        >
            <x-slot:actions>
                @if ($this->markDepositReceivedAction->isVisible())
                    {{ $this->markDepositReceivedAction }}
                @endif
            </x-slot:actions>

            @if ($viewModel->depositPaid)
                <dl class="divide-brand-700/40 divide-y">
                    <x-filament.catering-inquiries.detail-row label="Amount" class="tabular-nums">
                        {{ $viewModel->inquiry->deposit_amount?->formatted() ?? '—' }}
                    </x-filament.catering-inquiries.detail-row>
                    <x-filament.catering-inquiries.detail-row label="Received">
                        {{ $viewModel->inquiry->deposit_paid_at->format('M j, Y') }}
                    </x-filament.catering-inquiries.detail-row>
                    @if ($viewModel->inquiry->deposit_reference)
                        <x-filament.catering-inquiries.detail-row label="Reference">
                            {{ $viewModel->inquiry->deposit_reference }}
                        </x-filament.catering-inquiries.detail-row>
                    @endif
                </dl>
                @if ($viewModel->order)
                    <div class="text-brand-400 border-brand-700/40 mt-3 border-t pt-3 text-[0.8rem]">
                        Balance is tracked on the linked order.
                    </div>
                @endif
            @else
                <div class="text-brand-200 text-[0.9rem]">
                    Not received.
                    @if ($viewModel->suggestedDeposit !== null)
                        Suggested deposit:
                        <span class="font-semibold text-white tabular-nums">${{ number_format($viewModel->suggestedDeposit, 2) }}</span>
                        <span class="text-brand-400">({{ $viewModel->depositPercent }}% of quote)</span>
                    @endif
                </div>
            @endif
        </x-filament.catering-inquiries.section-card>

        {{-- ============== INTERNAL NOTES ============== --}}
        <x-filament.catering-inquiries.section-card title="Internal notes">
            <x-slot:actions>{{ $this->editNotesAction }}</x-slot:actions>

            @if (filled($viewModel->inquiry->notes))
                <pre class="text-brand-200 m-0 font-sans text-[0.85rem] leading-relaxed whitespace-pre-wrap">{{ $viewModel->inquiry->notes }}</pre>
            @else
                <div class="text-brand-400 text-[0.85rem]">No notes yet.</div>
            @endif
        </x-filament.catering-inquiries.section-card>
    </div>
</x-filament-panels::page>
