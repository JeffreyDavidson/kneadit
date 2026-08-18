<x-filament-panels::page>
    <div class="mb-6">
        <p class="text-cinnamon text-sm m-0">Create a Stripe coupon + promotion code in one shot. Hand the code to a baker for them to redeem at checkout.</p>
    </div>

    @if ($result)
        <x-central.card class="mb-6 bg-emerald-500/5 border-emerald-500/25">
            <div class="flex items-start gap-4 flex-wrap">
                <div class="shrink-0 w-11 h-11 rounded-xl bg-emerald-500/15 border border-emerald-500/25 flex items-center justify-center">
                    <x-heroicon-o-check-circle class="w-5 h-5 text-emerald-400" />
                </div>
                <div class="flex-1 min-w-[260px]">
                    <x-central.eyebrow class="mb-1">Promo code created</x-central.eyebrow>
                    <div class="text-white font-bold text-[1rem] mb-2">Hand this code to the baker — they type it at Stripe Checkout.</div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3 text-[0.8rem]">
                        <div>
                            <div class="text-cinnamon text-[0.7rem] uppercase tracking-[0.08em] font-semibold mb-1">Code</div>
                            <div class="text-emerald-400 font-mono font-bold text-[1rem]" x-data x-init="$el.addEventListener('click', () => navigator.clipboard.writeText('{{ $result->code }}'))" title="Click to copy">{{ $result->code }}</div>
                        </div>
                        <div>
                            <div class="text-cinnamon text-[0.7rem] uppercase tracking-[0.08em] font-semibold mb-1">Coupon ID</div>
                            <div class="text-parchment font-mono text-[0.75rem] break-all">{{ $result->couponId }}</div>
                        </div>
                        <div>
                            <div class="text-cinnamon text-[0.7rem] uppercase tracking-[0.08em] font-semibold mb-1">Promotion Code ID</div>
                            <div class="text-parchment font-mono text-[0.75rem] break-all">{{ $result->promotionCodeId }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </x-central.card>
    @endif

    <form wire:submit="generate">
        {{ $this->form }}

        <div class="mt-6 flex items-center justify-end gap-2">
            <x-central.button type="submit" class="gap-1.5">
                <x-heroicon-o-sparkles class="w-3.5 h-3.5" />
                Generate Promo Code
            </x-central.button>
        </div>
    </form>

    {{-- ============== HISTORY ============== --}}
    @php $codes = $this->getRecentCodes(); @endphp
    <div class="mt-8">
        <div class="flex items-center justify-between mb-3">
            <x-central.eyebrow>Recent Promo Codes</x-central.eyebrow>
            @if ($codes->isNotEmpty())
                <span class="text-cinnamon text-[0.7rem]">Last {{ $codes->count() }} created</span>
            @endif
        </div>

        @if ($codes->isEmpty())
            <x-central.card padding="py-12 px-6" class="text-center">
                <x-heroicon-o-ticket class="w-10 h-10 text-cinnamon/40 mx-auto mb-3 block" />
                <div class="text-white font-semibold">No promo codes yet</div>
                <div class="text-cinnamon text-[0.85rem] mt-1">Generated codes will appear here for reference.</div>
            </x-central.card>
        @else
            <x-central.card padding="p-0" class="overflow-hidden">
                <x-central.table>
                    <thead>
                        <x-central.tr>
                            <x-central.eyebrow as="th" class="px-4 py-3 text-left">Code</x-central.eyebrow>
                            <x-central.eyebrow as="th" class="px-4 py-3 text-left">Discount</x-central.eyebrow>
                            <x-central.eyebrow as="th" class="px-4 py-3 text-left">Duration</x-central.eyebrow>
                            <x-central.eyebrow as="th" class="px-4 py-3 text-left">Tenant</x-central.eyebrow>
                            <x-central.eyebrow as="th" class="px-4 py-3 text-right">Max Uses</x-central.eyebrow>
                            <x-central.eyebrow as="th" class="px-4 py-3 text-left">Expires</x-central.eyebrow>
                            <x-central.eyebrow as="th" class="px-4 py-3 text-left">Created</x-central.eyebrow>
                        </x-central.tr>
                    </thead>
                    <tbody>
                        @foreach ($codes as $code)
                            @php
                                $discountText = $code->percent_off !== null
                                    ? $code->percent_off . '% off'
                                    : '$' . number_format(($code->amount_off_cents ?? 0) / 100, 2) . ' off';
                                $durationText = match ($code->duration) {
                                    'once' => 'Once',
                                    'repeating' => $code->duration_in_months . ' months',
                                    'forever' => 'Forever',
                                    default => ucfirst($code->duration),
                                };
                                $isExpired = $code->expires_at && $code->expires_at->isPast();
                            @endphp
                            <x-central.tr>
                                <x-central.td>
                                    <span class="font-mono font-bold text-honey">{{ $code->code }}</span>
                                </x-central.td>
                                <x-central.td tone="white">{{ $discountText }}</x-central.td>
                                <x-central.td>{{ $durationText }}</x-central.td>
                                <x-central.td>
                                    @if ($code->tenant_id)
                                        <span class="text-parchment text-[0.8rem] font-mono">{{ $code->tenant_id }}</span>
                                    @else
                                        <span class="text-cinnamon/60">—</span>
                                    @endif
                                </x-central.td>
                                <x-central.td align="right" tone="white">{{ $code->max_redemptions }}</x-central.td>
                                <x-central.td>
                                    @if ($code->expires_at)
                                        <span @class([
                                            'text-[0.8rem]',
                                            'text-red-400' => $isExpired,
                                            'text-parchment' => ! $isExpired,
                                        ])>
                                            {{ $code->expires_at->format('M j, Y') }}
                                            @if ($isExpired)
                                                <span class="text-[0.65rem] uppercase tracking-[0.08em] font-bold ml-1">Expired</span>
                                            @endif
                                        </span>
                                    @else
                                        <span class="text-cinnamon/60">No expiry</span>
                                    @endif
                                </x-central.td>
                                <x-central.td>
                                    <div class="text-parchment text-[0.8rem]">{{ $code->created_at?->format('M j, Y') ?? '—' }}</div>
                                    @if ($code->createdBy)
                                        <div class="text-cinnamon text-[0.7rem]">by {{ $code->createdBy->name }}</div>
                                    @endif
                                </x-central.td>
                            </x-central.tr>
                        @endforeach
                    </tbody>
                </x-central.table>
            </x-central.card>
        @endif
    </div>
</x-filament-panels::page>
