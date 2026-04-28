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
    <div class="mb-6 bg-brand-900 border border-brand-800/60 rounded-xl p-6 flex flex-col md:flex-row md:items-center gap-5">
        <div class="flex-1 min-w-0">
            <div class="text-brand-300 text-[0.65rem] uppercase tracking-[0.1em] font-semibold mb-1">Order</div>
            <h2 class="text-white text-[1.35rem] font-bold leading-tight font-mono">{{ $order->order_number }}</h2>
            @if ($order->customer)
                <a href="{{ \App\Filament\Resources\Customers\CustomerResource::getUrl('view', ['record' => $order->customer]) }}"
                    class="inline-flex items-center gap-1.5 text-brand-400 text-[0.85rem] hover:text-brand-300 transition-colors mt-1">
                    {{ $order->customer->name }}
                    <x-heroicon-o-arrow-top-right-on-square class="w-3.5 h-3.5" />
                </a>
            @endif
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            <span class="inline-flex items-center gap-1.5 {{ $statusColor['bg'] }} border {{ $statusColor['border'] }} {{ $statusColor['text'] }} text-[0.7rem] font-bold uppercase tracking-[0.08em] rounded-full px-2.5 py-1">
                <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                {{ $order->status->getLabel() }}
            </span>
            <span class="inline-flex items-center gap-1.5 {{ $paymentColor['bg'] }} border {{ $paymentColor['border'] }} {{ $paymentColor['text'] }} text-[0.7rem] font-bold uppercase tracking-[0.08em] rounded-full px-2.5 py-1">
                {{ $order->payment_status->getLabel() }}
            </span>
            <span class="inline-flex items-center gap-1 bg-brand-800 border border-brand-300/15 text-brand-200 text-[0.7rem] font-semibold uppercase tracking-[0.08em] rounded-full px-2.5 py-1">
                @if ($isDelivery)
                    <x-heroicon-o-truck class="w-3 h-3" />
                @else
                    <x-heroicon-o-shopping-bag class="w-3 h-3" />
                @endif
                {{ $order->delivery_type->getLabel() }}
            </span>
        </div>

        <div class="text-right shrink-0">
            <div class="text-brand-300 text-[0.65rem] uppercase tracking-[0.1em] font-semibold mb-0.5">Total</div>
            <div class="text-white text-[1.5rem] font-bold leading-none">{{ $order->total->formatted() }}</div>
        </div>
    </div>

    {{-- ============== TABS ============== --}}
    <div x-data="{ tab: 'overview' }" class="space-y-6">
        <div class="border-b border-brand-300/12 flex items-center gap-1 overflow-x-auto">
            @php
                $tabs = [
                    'overview' => ['label' => 'Overview', 'icon' => 'chart-bar-square'],
                    'items' => ['label' => 'Items', 'icon' => 'shopping-bag', 'count' => $order->orderItems->count()],
                    'activity' => ['label' => 'Activity', 'icon' => 'clock', 'count' => $order->messages->count()],
                ];
            @endphp
            @foreach ($tabs as $key => $t)
                <button type="button" @click="tab = '{{ $key }}'"
                    :class="tab === '{{ $key }}'
                        ? 'text-white border-brand-300'
                        : 'text-brand-400 border-transparent hover:text-brand-200'"
                    class="inline-flex items-center gap-2 px-4 py-2.5 -mb-px border-b-2 text-[0.85rem] font-semibold transition-colors cursor-pointer whitespace-nowrap">
                    @switch($t['icon'])
                        @case('chart-bar-square') <x-heroicon-o-chart-bar-square class="w-4 h-4" /> @break
                        @case('shopping-bag') <x-heroicon-o-shopping-bag class="w-4 h-4" /> @break
                        @case('clock') <x-heroicon-o-clock class="w-4 h-4" /> @break
                    @endswitch
                    {{ $t['label'] }}
                    @isset($t['count'])
                        <span :class="tab === '{{ $key }}' ? 'bg-brand-300/15 text-brand-300' : 'bg-brand-800 text-brand-400'"
                            class="inline-flex items-center justify-center min-w-[1.25rem] h-5 px-1.5 rounded-full text-[0.7rem] font-bold transition-colors">
                            {{ $t['count'] }}
                        </span>
                    @endisset
                </button>
            @endforeach
        </div>

        {{-- ============== TAB: OVERVIEW ============== --}}
        <div x-show="tab === 'overview'" x-cloak class="space-y-6">
            {{-- Customer + Delivery --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {{-- Customer --}}
                <div class="bg-brand-900 border border-brand-800/60 rounded-xl p-6">
                    <div class="text-brand-300 text-[0.65rem] uppercase tracking-[0.1em] font-semibold mb-4">Customer</div>
                    @if ($order->customer)
                        <dl class="divide-y divide-brand-700/40">
                            <div class="flex items-start justify-between gap-4 py-2.5 first:pt-0 last:pb-0">
                                <dt class="text-brand-400 text-[0.8rem] shrink-0 pt-0.5">Name</dt>
                                <dd class="text-white text-[0.85rem] font-semibold text-right truncate">{{ $order->customer->name }}</dd>
                            </div>
                            @if ($order->customer->email)
                                <div class="flex items-start justify-between gap-4 py-2.5 last:pb-0">
                                    <dt class="text-brand-400 text-[0.8rem] shrink-0 pt-0.5">Email</dt>
                                    <dd class="text-white text-[0.85rem] font-semibold text-right truncate">{{ $order->customer->email }}</dd>
                                </div>
                            @endif
                            @if ($order->customer->phone)
                                <div class="flex items-start justify-between gap-4 py-2.5 last:pb-0">
                                    <dt class="text-brand-400 text-[0.8rem] shrink-0 pt-0.5">Phone</dt>
                                    <dd class="text-white text-[0.85rem] font-semibold text-right">{{ $order->customer->phone }}</dd>
                                </div>
                            @endif
                        </dl>
                    @else
                        <div class="text-brand-400 text-[0.85rem]">No customer attached.</div>
                    @endif
                </div>

                {{-- Delivery / Pickup --}}
                <div class="bg-brand-900 border border-brand-800/60 rounded-xl p-6">
                    <div class="text-brand-300 text-[0.65rem] uppercase tracking-[0.1em] font-semibold mb-4">{{ $isDelivery ? 'Delivery' : 'Pickup' }}</div>
                    <dl class="divide-y divide-brand-700/40">
                        @if ($isDelivery && $order->delivery_address)
                            <div class="flex items-start justify-between gap-4 py-2.5 first:pt-0 last:pb-0">
                                <dt class="text-brand-400 text-[0.8rem] shrink-0 pt-0.5">Address</dt>
                                <dd class="text-white text-[0.85rem] font-semibold text-right whitespace-pre-wrap">{{ $order->delivery_address }}</dd>
                            </div>
                        @endif
                        <div class="flex items-start justify-between gap-4 py-2.5 first:pt-0 last:pb-0">
                            <dt class="text-brand-400 text-[0.8rem] shrink-0 pt-0.5">Date</dt>
                            <dd class="text-white text-[0.85rem] font-semibold text-right">{{ $order->delivery_date?->format('M j, Y') ?? '—' }}</dd>
                        </div>
                        <div class="flex items-start justify-between gap-4 py-2.5 last:pb-0">
                            <dt class="text-brand-400 text-[0.8rem] shrink-0 pt-0.5">Time</dt>
                            <dd class="text-white text-[0.85rem] font-semibold text-right">{{ $order->delivery_time?->format('g:i A') ?? '—' }}</dd>
                        </div>
                        @if ($hasPickupContact)
                            <div class="flex items-start justify-between gap-4 py-2.5 last:pb-0">
                                <dt class="text-brand-400 text-[0.8rem] shrink-0 pt-0.5">Pickup Contact</dt>
                                <dd class="text-white text-[0.85rem] font-semibold text-right">
                                    {{ $order->pickup_contact_name }}
                                    @if ($order->pickup_contact_phone)
                                        <div class="text-brand-400 font-normal text-[0.8rem]">{{ $order->pickup_contact_phone }}</div>
                                    @endif
                                </dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </div>

            {{-- Pricing breakdown --}}
            <div class="bg-brand-900 border border-brand-800/60 rounded-xl p-6">
                <div class="text-brand-300 text-[0.65rem] uppercase tracking-[0.1em] font-semibold mb-4">Pricing</div>
                <dl class="space-y-2.5">
                    <div class="flex items-center justify-between text-[0.875rem]">
                        <dt class="text-brand-400">Subtotal</dt>
                        <dd class="text-white font-semibold tabular-nums">{{ $order->subtotal->formatted() }}</dd>
                    </div>
                    @if ($order->delivery_fee->dollars() > 0)
                        <div class="flex items-center justify-between text-[0.875rem]">
                            <dt class="text-brand-400">Delivery Fee</dt>
                            <dd class="text-white font-semibold tabular-nums">{{ $order->delivery_fee->formatted() }}</dd>
                        </div>
                    @endif
                    @if ($order->discount_amount->dollars() > 0)
                        <div class="flex items-center justify-between text-[0.875rem]">
                            <dt class="text-brand-400">Discount</dt>
                            <dd class="text-emerald-400 font-semibold tabular-nums">−{{ $order->discount_amount->formatted() }}</dd>
                        </div>
                    @endif
                    @if ($order->gift_card_amount->dollars() > 0)
                        <div class="flex items-center justify-between text-[0.875rem]">
                            <dt class="text-brand-400">Gift Card</dt>
                            <dd class="text-emerald-400 font-semibold tabular-nums">−{{ $order->gift_card_amount->formatted() }}</dd>
                        </div>
                    @endif
                    @if ($order->tip_amount->dollars() > 0)
                        <div class="flex items-center justify-between text-[0.875rem]">
                            <dt class="text-brand-400">Tip</dt>
                            <dd class="text-white font-semibold tabular-nums">{{ $order->tip_amount->formatted() }}</dd>
                        </div>
                    @endif
                    <div class="flex items-center justify-between pt-3 border-t border-brand-700/40">
                        <dt class="text-brand-200 text-[0.95rem] font-bold uppercase tracking-[0.05em]">Total</dt>
                        <dd class="text-white text-[1.25rem] font-bold tabular-nums">{{ $order->total->formatted() }}</dd>
                    </div>
                    <div class="flex items-center justify-between text-[0.75rem] text-brand-400 pt-1">
                        <span>Payment</span>
                        <span class="font-semibold uppercase tracking-[0.05em]">{{ $order->payment_method?->getLabel() ?? '—' }}</span>
                    </div>
                </dl>
            </div>
        </div>

        {{-- ============== TAB: ITEMS ============== --}}
        <div x-show="tab === 'items'" x-cloak>
            <div class="bg-brand-900 border border-brand-800/60 rounded-xl p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="text-brand-300 text-[0.65rem] uppercase tracking-[0.1em] font-semibold">Line Items</div>
                    <span class="text-brand-400 text-[0.75rem]">{{ $order->orderItems->count() }} {{ Str::plural('item', $order->orderItems->count()) }}</span>
                </div>

                @if ($order->orderItems->isEmpty())
                    <div class="text-center py-12">
                        <x-heroicon-o-shopping-bag class="w-10 h-10 text-brand-400/40 mx-auto mb-3" />
                        <div class="text-brand-200 text-[0.9rem] font-semibold">No items on this order</div>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-left text-[0.85rem]">
                            <thead class="border-b border-brand-700/40 text-[0.7rem] uppercase tracking-[0.05em] text-brand-400">
                                <tr>
                                    <th class="py-2 pr-4 font-semibold">Product</th>
                                    <th class="py-2 pr-4 font-semibold text-right">Qty</th>
                                    <th class="py-2 pr-4 font-semibold text-right">Unit Price</th>
                                    <th class="py-2 font-semibold text-right">Line Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-brand-700/40">
                                @foreach ($order->orderItems as $item)
                                    @php $lineTotal = $item->unit_price->dollars() * $item->quantity; @endphp
                                    <tr>
                                        <td class="py-3 pr-4">
                                            <div class="flex items-center gap-3">
                                                @if ($item->product?->image)
                                                    <img src="{{ Storage::url($item->product->image) }}"
                                                        alt="{{ $item->product->name }}"
                                                        class="w-10 h-10 rounded-lg object-cover shrink-0" />
                                                @else
                                                    <div class="w-10 h-10 rounded-lg bg-brand-800 border border-brand-700 flex items-center justify-center shrink-0">
                                                        <x-heroicon-o-photo class="w-5 h-5 text-brand-400" />
                                                    </div>
                                                @endif
                                                <div class="min-w-0">
                                                    <div class="text-white font-semibold truncate">{{ $item->product?->name ?? '— removed —' }}</div>
                                                    @if ($item->special_instructions)
                                                        <div class="text-brand-400 text-[0.75rem] mt-0.5 italic truncate">"{{ $item->special_instructions }}"</div>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td class="py-3 pr-4 text-right text-white tabular-nums">{{ $item->quantity }}</td>
                                        <td class="py-3 pr-4 text-right text-brand-200 tabular-nums">{{ $item->unit_price->formatted() }}</td>
                                        <td class="py-3 text-right text-white font-semibold tabular-nums">${{ number_format($lineTotal, 2) }}</td>
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
            <div class="bg-brand-900 border border-brand-800/60 rounded-xl p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="text-brand-300 text-[0.65rem] uppercase tracking-[0.1em] font-semibold">Messages</div>
                    <span class="text-brand-400 text-[0.75rem]">{{ $order->messages->count() }} total</span>
                </div>

                @php $messages = $order->messages->sortBy('created_at'); @endphp

                @if ($messages->isEmpty())
                    <div class="text-center py-10">
                        <x-heroicon-o-chat-bubble-left-right class="w-10 h-10 text-brand-400/40 mx-auto mb-3" />
                        <div class="text-brand-200 text-[0.9rem] font-semibold">No messages yet</div>
                        <div class="text-brand-400 text-[0.8rem] mt-1">Use Send Message above to start a conversation with the customer.</div>
                    </div>
                @else
                    <div class="space-y-3 max-h-96 overflow-y-auto">
                        @foreach ($messages as $msg)
                            @php $isBaker = $msg->sender_type->isBaker(); @endphp
                            <div class="flex {{ $isBaker ? 'justify-end' : 'justify-start' }}">
                                <div class="max-w-md rounded-lg px-4 py-3 {{ $isBaker ? 'bg-brand-700 border border-brand-600' : 'bg-brand-800 border border-brand-700' }}">
                                    <div class="flex items-center gap-2 mb-1.5">
                                        <span class="text-[0.75rem] font-semibold text-brand-200">{{ $msg->sender_name }}</span>
                                        @if ($isBaker)
                                            <span class="px-1.5 py-0.5 rounded text-[0.65rem] uppercase tracking-[0.05em] bg-brand-300/15 text-brand-300 font-semibold">Baker</span>
                                        @endif
                                    </div>
                                    <p class="text-[0.875rem] text-white whitespace-pre-wrap">{{ $msg->message }}</p>
                                    <p class="text-[0.7rem] mt-1.5 text-brand-400">
                                        {{ $msg->created_at->format('M j · g:i A') }}
                                        @if (! $isBaker && ! $msg->is_read)
                                            <span class="ml-1.5 inline-block w-2 h-2 rounded-full bg-sky-400" title="Unread"></span>
                                        @endif
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Notes (timestamped, appended) --}}
            <div class="bg-brand-900 border border-brand-800/60 rounded-xl p-6">
                <div class="text-brand-300 text-[0.65rem] uppercase tracking-[0.1em] font-semibold mb-4">Notes</div>
                @if (filled($order->notes))
                    <pre class="text-brand-200 text-[0.85rem] leading-relaxed whitespace-pre-wrap font-sans m-0">{{ $order->notes }}</pre>
                @else
                    <div class="text-center py-8">
                        <x-heroicon-o-pencil-square class="w-10 h-10 text-brand-400/40 mx-auto mb-3" />
                        <div class="text-brand-200 text-[0.9rem] font-semibold">No notes yet</div>
                        <div class="text-brand-400 text-[0.8rem] mt-1">Use Add Note above to record context for this order.</div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-filament-panels::page>
