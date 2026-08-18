@php
    use App\Enums\Orders\DeliveryType;

    $order = $record;

    $statusColor = match ($order->status->value) {
        'pending' => ['bg' => 'bg-amber-500/15', 'border' => 'border-amber-500/25', 'text' => 'text-amber-400'],
        'confirmed' => ['bg' => 'bg-sky-500/15', 'border' => 'border-sky-500/25', 'text' => 'text-sky-400'],
        'baking' => ['bg' => 'bg-orange-500/15', 'border' => 'border-orange-500/25', 'text' => 'text-orange-400'],
        'ready' => ['bg' => 'bg-emerald-500/15', 'border' => 'border-emerald-500/25', 'text' => 'text-emerald-400'],
        'delivered' => ['bg' => 'bg-emerald-500/15', 'border' => 'border-emerald-500/25', 'text' => 'text-emerald-400'],
        'cancelled' => ['bg' => 'bg-red-500/15', 'border' => 'border-red-500/25', 'text' => 'text-red-400'],
        default => ['bg' => 'bg-brand-800', 'border' => 'border-brand-700', 'text' => 'text-brand-200'],
    };

    $paymentColor = match ($order->payment_status->value) {
        'paid' => ['bg' => 'bg-emerald-500/15', 'border' => 'border-emerald-500/25', 'text' => 'text-emerald-400'],
        'unpaid' => ['bg' => 'bg-red-500/15', 'border' => 'border-red-500/25', 'text' => 'text-red-400'],
        'refunded' => ['bg' => 'bg-amber-500/15', 'border' => 'border-amber-500/25', 'text' => 'text-amber-400'],
        default => ['bg' => 'bg-brand-800', 'border' => 'border-brand-700', 'text' => 'text-brand-200'],
    };

    $isDelivery = $order->delivery_type === DeliveryType::Delivery;
    $hasPickupContact = filled($order->pickup_contact_name);
@endphp

<x-filament-panels::page>
    {{-- ============== HERO STRIP ============== --}}
    <div class="bg-brand-900 border-brand-800/60 mb-6 flex flex-col gap-5 rounded-xl border p-6 md:flex-row md:items-center">
        <div class="min-w-0 flex-1">
            <div class="text-brand-300 mb-1 text-[0.65rem] font-semibold tracking-[0.1em] uppercase">Order</div>
            <h2 class="font-mono text-[1.35rem] leading-tight font-bold text-white">{{ $order->order_number }}</h2>
            @if ($order->customer)
                <a
                    href="{{ \App\Filament\Resources\Customers\CustomerResource::getUrl('view', ['record' => $order->customer]) }}"
                    class="text-brand-400 hover:text-brand-300 mt-1 inline-flex items-center gap-1.5 text-[0.85rem] transition-colors"
                >
                    {{ $order->customer->name }}
                    <x-heroicon-o-arrow-top-right-on-square class="h-3.5 w-3.5" />
                </a>
            @endif
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <span class="inline-flex items-center gap-1.5 {{ $statusColor['bg'] }} border {{ $statusColor['border'] }} {{ $statusColor['text'] }} text-[0.7rem] font-bold uppercase tracking-[0.08em] rounded-full px-2.5 py-1">
                <span class="h-1.5 w-1.5 rounded-full bg-current"></span>
                {{ $order->status->getLabel() }}
            </span>
            <span class="inline-flex items-center gap-1.5 {{ $paymentColor['bg'] }} border {{ $paymentColor['border'] }} {{ $paymentColor['text'] }} text-[0.7rem] font-bold uppercase tracking-[0.08em] rounded-full px-2.5 py-1">
                {{ $order->payment_status->getLabel() }}
            </span>
            <span class="bg-brand-800 border-brand-300/15 text-brand-200 inline-flex items-center gap-1 rounded-full border px-2.5 py-1 text-[0.7rem] font-semibold tracking-[0.08em] uppercase">
                @if ($isDelivery)
                    <x-heroicon-o-truck class="h-3 w-3" />
                @else
                    <x-heroicon-o-shopping-bag class="h-3 w-3" />
                @endif
                {{ $order->delivery_type->getLabel() }}
            </span>
        </div>

        <div class="shrink-0 text-right">
            <div class="text-brand-300 mb-0.5 text-[0.65rem] font-semibold tracking-[0.1em] uppercase">Total</div>
            <div class="text-[1.5rem] leading-none font-bold text-white">{{ $order->total->formatted() }}</div>
        </div>
    </div>

    {{-- ============== TABS ============== --}}
    <div x-data="{ tab: 'overview' }" class="space-y-6">
        <div class="border-brand-300/12 flex items-center gap-1 overflow-x-auto border-b">
            @php
                $tabs = [
                    'overview' => ['label' => 'Overview', 'icon' => 'chart-bar-square'],
                    'items' => ['label' => 'Items', 'icon' => 'shopping-bag', 'count' => $order->orderItems->count()],
                    'activity' => ['label' => 'Activity', 'icon' => 'clock', 'count' => $order->messages->count()],
                ];
            @endphp
            @foreach ($tabs as $key => $t)
                <button
                    type="button"
                    @click="tab = '{{ $key }}'"
                    :class="tab === '{{ $key }}'
                        ? 'text-white border-brand-300'
                        : 'text-brand-400 border-transparent hover:text-brand-200'"
                    class="-mb-px inline-flex cursor-pointer items-center gap-2 border-b-2 px-4 py-2.5 text-[0.85rem] font-semibold whitespace-nowrap transition-colors"
                >
                    @switch ($t['icon'])
                        @case ('chart-bar-square')
                            <x-heroicon-o-chart-bar-square class="h-4 w-4" />
                            @break
                        @case ('shopping-bag')
                            <x-heroicon-o-shopping-bag class="h-4 w-4" />
                            @break
                        @case ('clock')
                            <x-heroicon-o-clock class="h-4 w-4" />
                            @break
                    @endswitch
                    {{ $t['label'] }}
                    @isset($t['count'])
                        <span
                            :class="tab === '{{ $key }}' ? 'bg-brand-300/15 text-brand-300' : 'bg-brand-800 text-brand-400'"
                            class="inline-flex h-5 min-w-[1.25rem] items-center justify-center rounded-full px-1.5 text-[0.7rem] font-bold transition-colors"
                        >
                            {{ $t['count'] }}
                        </span>
                    @endisset
                </button>
            @endforeach
        </div>

        {{-- ============== TAB: OVERVIEW ============== --}}
        <div x-show="tab === 'overview'" x-cloak class="space-y-6">
            @if ($order->cateringInquiry)
                @php $inq = $order->cateringInquiry; @endphp
                <div class="bg-brand-900 border-brand-800/60 rounded-xl border p-6">
                    <div class="mb-4 flex items-center justify-between">
                        <div class="text-brand-300 text-[0.65rem] font-semibold tracking-[0.1em] uppercase">
                            Catering
                        </div>
                        <a
                            href="{{ \App\Filament\Resources\CateringInquiries\CateringInquiryResource::getUrl('view', ['record' => $inq]) }}"
                            class="text-brand-400 hover:text-brand-200 inline-flex items-center gap-1.5 text-[0.8rem] transition-colors"
                        >
                            View inquiry
                            <x-heroicon-o-arrow-top-right-on-square class="h-3.5 w-3.5" />
                        </a>
                    </div>
                    <dl class="divide-brand-700/40 divide-y">
                        <div class="flex items-start justify-between gap-4 py-2.5 first:pt-0 last:pb-0">
                            <dt class="text-brand-400 shrink-0 pt-0.5 text-[0.8rem]">Event</dt>
                            <dd class="text-right text-[0.85rem] font-semibold text-white">
                                {{ $inq->event_type }}
                                @if ($inq->event_date)
                                    · {{ $inq->event_date->format('M j, Y') }}
                                @endif
                            </dd>
                        </div>
                        <div class="flex items-start justify-between gap-4 py-2.5 last:pb-0">
                            <dt class="text-brand-400 shrink-0 pt-0.5 text-[0.8rem]">Guests</dt>
                            <dd class="text-right text-[0.85rem] font-semibold text-white tabular-nums">
                                {{ number_format($inq->guest_count) }}
                            </dd>
                        </div>
                        @if ($inq->venue_address)
                            <div class="flex items-start justify-between gap-4 py-2.5 last:pb-0">
                                <dt class="text-brand-400 shrink-0 pt-0.5 text-[0.8rem]">Venue</dt>
                                <dd class="max-w-md text-right text-[0.85rem] font-semibold whitespace-pre-wrap text-white">
                                    {{ $inq->venue_address }}
                                </dd>
                            </div>
                        @endif
                        @if ($inq->dietary_requirements)
                            <div class="flex items-start justify-between gap-4 py-2.5 last:pb-0">
                                <dt class="text-brand-400 shrink-0 pt-0.5 text-[0.8rem]">Dietary</dt>
                                <dd class="max-w-md text-right text-[0.85rem] font-semibold whitespace-pre-wrap text-white">
                                    {{ $inq->dietary_requirements }}
                                </dd>
                            </div>
                        @endif
                        @if ($inq->deposit_paid_at)
                            <div class="flex items-start justify-between gap-4 py-2.5 last:pb-0">
                                <dt class="text-brand-400 shrink-0 pt-0.5 text-[0.8rem]">Deposit</dt>
                                <dd class="text-right text-[0.85rem] font-semibold text-white tabular-nums">
                                    {{ $inq->deposit_amount?->formatted() ?? '—' }}
                                    <div class="text-brand-400 text-[0.75rem] font-normal">
                                        received {{ $inq->deposit_paid_at->format('M j, Y') }}
                                    </div>
                                </dd>
                            </div>
                        @endif
                    </dl>
                </div>
            @endif

            {{-- Customer + Delivery --}}
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                {{-- Customer --}}
                <div class="bg-brand-900 border-brand-800/60 rounded-xl border p-6">
                    <div class="text-brand-300 mb-4 text-[0.65rem] font-semibold tracking-[0.1em] uppercase">
                        Customer
                    </div>
                    @if ($order->customer)
                        <dl class="divide-brand-700/40 divide-y">
                            <div class="flex items-start justify-between gap-4 py-2.5 first:pt-0 last:pb-0">
                                <dt class="text-brand-400 shrink-0 pt-0.5 text-[0.8rem]">Name</dt>
                                <dd class="truncate text-right text-[0.85rem] font-semibold text-white">
                                    {{ $order->customer->name }}
                                </dd>
                            </div>
                            @if ($order->customer->email)
                                <div class="flex items-start justify-between gap-4 py-2.5 last:pb-0">
                                    <dt class="text-brand-400 shrink-0 pt-0.5 text-[0.8rem]">Email</dt>
                                    <dd class="truncate text-right text-[0.85rem] font-semibold text-white">
                                        {{ $order->customer->email }}
                                    </dd>
                                </div>
                            @endif
                            @if ($order->customer->phone)
                                <div class="flex items-start justify-between gap-4 py-2.5 last:pb-0">
                                    <dt class="text-brand-400 shrink-0 pt-0.5 text-[0.8rem]">Phone</dt>
                                    <dd class="text-right text-[0.85rem] font-semibold text-white">
                                        {{ $order->customer->phone }}
                                    </dd>
                                </div>
                            @endif
                        </dl>
                    @else
                        <div class="text-brand-400 text-[0.85rem]">No customer attached.</div>
                    @endif
                </div>

                {{-- Delivery / Pickup --}}
                <div class="bg-brand-900 border-brand-800/60 rounded-xl border p-6">
                    <div class="text-brand-300 mb-4 text-[0.65rem] font-semibold tracking-[0.1em] uppercase">
                        {{ $isDelivery ? 'Delivery' : 'Pickup' }}
                    </div>
                    <dl class="divide-brand-700/40 divide-y">
                        @if ($isDelivery && $order->delivery_address)
                            <div class="flex items-start justify-between gap-4 py-2.5 first:pt-0 last:pb-0">
                                <dt class="text-brand-400 shrink-0 pt-0.5 text-[0.8rem]">Address</dt>
                                <dd class="text-right text-[0.85rem] font-semibold whitespace-pre-wrap text-white">
                                    {{ $order->delivery_address }}
                                </dd>
                            </div>
                        @endif
                        <div class="flex items-start justify-between gap-4 py-2.5 first:pt-0 last:pb-0">
                            <dt class="text-brand-400 shrink-0 pt-0.5 text-[0.8rem]">Date</dt>
                            <dd class="text-right text-[0.85rem] font-semibold text-white">
                                {{ $order->delivery_date?->format('M j, Y') ?? '—' }}
                            </dd>
                        </div>
                        <div class="flex items-start justify-between gap-4 py-2.5 last:pb-0">
                            <dt class="text-brand-400 shrink-0 pt-0.5 text-[0.8rem]">Time</dt>
                            <dd class="text-right text-[0.85rem] font-semibold text-white">
                                {{ $order->delivery_time?->format('g:i A') ?? '—' }}
                            </dd>
                        </div>
                        @if ($hasPickupContact)
                            <div class="flex items-start justify-between gap-4 py-2.5 last:pb-0">
                                <dt class="text-brand-400 shrink-0 pt-0.5 text-[0.8rem]">Pickup Contact</dt>
                                <dd class="text-right text-[0.85rem] font-semibold text-white">
                                    {{ $order->pickup_contact_name }}
                                    @if ($order->pickup_contact_phone)
                                        <div class="text-brand-400 text-[0.8rem] font-normal">
                                            {{ $order->pickup_contact_phone }}
                                        </div>
                                    @endif
                                </dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </div>

            {{-- Pricing breakdown --}}
            <div class="bg-brand-900 border-brand-800/60 rounded-xl border p-6">
                <div class="text-brand-300 mb-4 text-[0.65rem] font-semibold tracking-[0.1em] uppercase">Pricing</div>
                <dl class="space-y-2.5">
                    <div class="flex items-center justify-between text-[0.875rem]">
                        <dt class="text-brand-400">Subtotal</dt>
                        <dd class="font-semibold text-white tabular-nums">{{ $order->subtotal->formatted() }}</dd>
                    </div>
                    @if ($order->delivery_fee->dollars() > 0)
                        <div class="flex items-center justify-between text-[0.875rem]">
                            <dt class="text-brand-400">Delivery Fee</dt>
                            <dd class="font-semibold text-white tabular-nums">
                                {{ $order->delivery_fee->formatted() }}
                            </dd>
                        </div>
                    @endif
                    @if ($order->discount_amount->dollars() > 0)
                        <div class="flex items-center justify-between text-[0.875rem]">
                            <dt class="text-brand-400">Discount</dt>
                            <dd class="font-semibold text-emerald-400 tabular-nums">
                                −{{ $order->discount_amount->formatted() }}
                            </dd>
                        </div>
                    @endif
                    @if ($order->gift_card_amount->dollars() > 0)
                        <div class="flex items-center justify-between text-[0.875rem]">
                            <dt class="text-brand-400">Gift Card</dt>
                            <dd class="font-semibold text-emerald-400 tabular-nums">
                                −{{ $order->gift_card_amount->formatted() }}
                            </dd>
                        </div>
                    @endif
                    @if ($order->tip_amount->dollars() > 0)
                        <div class="flex items-center justify-between text-[0.875rem]">
                            <dt class="text-brand-400">Tip</dt>
                            <dd class="font-semibold text-white tabular-nums">{{ $order->tip_amount->formatted() }}</dd>
                        </div>
                    @endif
                    <div class="border-brand-700/40 flex items-center justify-between border-t pt-3">
                        <dt class="text-brand-200 text-[0.95rem] font-bold tracking-[0.05em] uppercase">Total</dt>
                        <dd class="text-[1.25rem] font-bold text-white tabular-nums">
                            {{ $order->total->formatted() }}
                        </dd>
                    </div>
                    <div class="text-brand-400 flex items-center justify-between pt-1 text-[0.75rem]">
                        <span>Payment</span>
                        <span class="font-semibold tracking-[0.05em] uppercase">{{ $order->payment_method?->getLabel() ?? '—' }}</span>
                    </div>
                </dl>
            </div>
        </div>

        {{-- ============== TAB: ITEMS ============== --}}
        <div x-show="tab === 'items'" x-cloak>
            <div class="bg-brand-900 border-brand-800/60 rounded-xl border p-6">
                <div class="mb-4 flex items-center justify-between">
                    <div class="text-brand-300 text-[0.65rem] font-semibold tracking-[0.1em] uppercase">Line Items</div>
                    <span class="text-brand-400 text-[0.75rem]">{{ $order->orderItems->count() }} {{ Str::plural('item', $order->orderItems->count()) }}</span>
                </div>

                @if ($order->orderItems->isEmpty())
                    <div class="py-12 text-center">
                        <x-heroicon-o-shopping-bag class="text-brand-400/40 mx-auto mb-3 h-10 w-10" />
                        <div class="text-brand-200 text-[0.9rem] font-semibold">No items on this order</div>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-left text-[0.85rem]">
                            <thead class="border-brand-700/40 text-brand-400 border-b text-[0.7rem] tracking-[0.05em] uppercase">
                                <tr>
                                    <th class="py-2 pr-4 font-semibold">Product</th>
                                    <th class="py-2 pr-4 text-right font-semibold">Qty</th>
                                    <th class="py-2 pr-4 text-right font-semibold">Unit Price</th>
                                    <th class="py-2 text-right font-semibold">Line Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-brand-700/40 divide-y">
                                @foreach ($order->orderItems as $item)
                                    @php $lineTotal = $item->unit_price->dollars() * $item->quantity; @endphp
                                    <tr>
                                        <td class="py-3 pr-4">
                                            <div class="flex items-center gap-3">
                                                @if ($item->product?->image)
                                                    <img
                                                        src="{{ Storage::url($item->product->image) }}"
                                                        alt="{{ $item->product->name }}"
                                                        class="h-10 w-10 shrink-0 rounded-lg object-cover"
                                                    />
                                                @else
                                                    <div class="bg-brand-800 border-brand-700 flex h-10 w-10 shrink-0 items-center justify-center rounded-lg border">
                                                        <x-heroicon-o-photo class="text-brand-400 h-5 w-5" />
                                                    </div>
                                                @endif
                                                <div class="min-w-0">
                                                    <div class="truncate font-semibold text-white">
                                                        {{ $item->name ?? $item->product?->name ?? '— removed —' }}
                                                    </div>
                                                    @if ($item->special_instructions)
                                                        <div class="text-brand-400 mt-0.5 truncate text-[0.75rem] italic">
                                                            "{{ $item->special_instructions }}"
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td class="py-3 pr-4 text-right text-white tabular-nums">
                                            {{ $item->quantity }}
                                        </td>
                                        <td class="text-brand-200 py-3 pr-4 text-right tabular-nums">
                                            {{ $item->unit_price->formatted() }}
                                        </td>
                                        <td class="py-3 text-right font-semibold text-white tabular-nums">
                                            ${{ number_format($lineTotal, 2) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        {{-- ============== TAB: ACTIVITY ============== --}}
        <div x-show="tab === 'activity'" x-cloak class="space-y-6">
            {{-- Messages thread --}}
            <div class="bg-brand-900 border-brand-800/60 rounded-xl border p-6">
                <div class="mb-4 flex items-center justify-between">
                    <div class="text-brand-300 text-[0.65rem] font-semibold tracking-[0.1em] uppercase">Messages</div>
                    <span class="text-brand-400 text-[0.75rem]">{{ $order->messages->count() }} total</span>
                </div>

                @php $messages = $order->messages->sortBy('created_at'); @endphp

                @if ($messages->isEmpty())
                    <div class="py-10 text-center">
                        <x-heroicon-o-chat-bubble-left-right class="text-brand-400/40 mx-auto mb-3 h-10 w-10" />
                        <div class="text-brand-200 text-[0.9rem] font-semibold">No messages yet</div>
                        <div class="text-brand-400 mt-1 text-[0.8rem]">
                            Use Send Message above to start a conversation with the customer.
                        </div>
                    </div>
                @else
                    <div class="max-h-96 space-y-3 overflow-y-auto">
                        @foreach ($messages as $msg)
                            @php $isBaker = $msg->sender_type->isBaker(); @endphp
                            <div class="flex {{ $isBaker ? 'justify-end' : 'justify-start' }}">
                                <div class="max-w-md rounded-lg px-4 py-3 {{ $isBaker ? 'bg-brand-700 border border-brand-600' : 'bg-brand-800 border border-brand-700' }}">
                                    <div class="mb-1.5 flex items-center gap-2">
                                        <span class="text-brand-200 text-[0.75rem] font-semibold">{{ $msg->sender_name }}</span>
                                        @if ($isBaker)
                                            <span class="bg-brand-300/15 text-brand-300 rounded px-1.5 py-0.5 text-[0.65rem] font-semibold tracking-[0.05em] uppercase">Baker</span>
                                        @endif
                                    </div>
                                    <p class="text-[0.875rem] whitespace-pre-wrap text-white">{{ $msg->message }}</p>
                                    <p class="text-brand-400 mt-1.5 text-[0.7rem]">
                                        {{ $msg->created_at->format('M j · g:i A') }}
                                        @if (! $isBaker && ! $msg->is_read)
                                            <span
                                                class="ml-1.5 inline-block h-2 w-2 rounded-full bg-sky-400"
                                                title="Unread"
                                            ></span>
                                        @endif
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Notes (timestamped, appended) --}}
            <div class="bg-brand-900 border-brand-800/60 rounded-xl border p-6">
                <div class="text-brand-300 mb-4 text-[0.65rem] font-semibold tracking-[0.1em] uppercase">Notes</div>
                @if (filled($order->notes))
                    <pre class="text-brand-200 m-0 font-sans text-[0.85rem] leading-relaxed whitespace-pre-wrap">{{ $order->notes }}</pre>
                @else
                    <div class="py-8 text-center">
                        <x-heroicon-o-pencil-square class="text-brand-400/40 mx-auto mb-3 h-10 w-10" />
                        <div class="text-brand-200 text-[0.9rem] font-semibold">No notes yet</div>
                        <div class="text-brand-400 mt-1 text-[0.8rem]">
                            Use Add Note above to record context for this order.
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-filament-panels::page>
