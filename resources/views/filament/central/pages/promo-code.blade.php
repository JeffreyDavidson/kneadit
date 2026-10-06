<x-filament-panels::page>
    <div class="mb-6">
        <p class="m-0 text-sm text-(--kn-muted)">
            Create a Stripe coupon + promotion code in one shot. Hand the code to a baker for them to redeem at
            checkout.
        </p>
    </div>

    @if ($result)
        <x-central.card class="mb-6 border-(--kn-success)/25 bg-(--kn-success-tint)">
            <div class="flex flex-wrap items-start gap-4">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border border-(--kn-success)/25 bg-(--kn-success-tint)">
                    <x-heroicon-o-check-circle class="h-5 w-5 text-(--kn-success)" />
                </div>
                <div class="min-w-[260px] flex-1">
                    <x-central.eyebrow class="mb-1">Promo code created</x-central.eyebrow>
                    <div class="mb-2 text-[1rem] font-bold text-(--kn-ink)">
                        Hand this code to the baker — they type it at Stripe Checkout.
                    </div>
                    <div class="grid grid-cols-1 gap-3 text-[0.8rem] md:grid-cols-3">
                        <div>
                            <div class="mb-1 text-[0.7rem] font-semibold tracking-[0.08em] text-(--kn-muted) uppercase">
                                Code
                            </div>
                            <div
                                class="font-mono text-[1rem] font-bold text-(--kn-success)"
                                x-data
                                x-init="$el.addEventListener('click', () => navigator.clipboard.writeText({{ Js::from($result->code) }}))"
                                title="Click to copy"
                            >
                                {{ $result->code }}
                            </div>
                        </div>
                        <div>
                            <div class="mb-1 text-[0.7rem] font-semibold tracking-[0.08em] text-(--kn-muted) uppercase">
                                Coupon ID
                            </div>
                            <div class="font-mono text-[0.75rem] break-all text-(--kn-ink)">
                                {{ $result->couponId }}
                            </div>
                        </div>
                        <div>
                            <div class="mb-1 text-[0.7rem] font-semibold tracking-[0.08em] text-(--kn-muted) uppercase">
                                Promotion Code ID
                            </div>
                            <div class="font-mono text-[0.75rem] break-all text-(--kn-ink)">
                                {{ $result->promotionCodeId }}
                            </div>
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
                <x-heroicon-o-sparkles class="h-3.5 w-3.5" />
                Generate Promo Code
            </x-central.button>
        </div>
    </form>

    {{-- ============== HISTORY ============== --}}
    @php $codes = $this->getRecentCodes(); @endphp
    <div class="mt-8">
        <div class="mb-3 flex items-center justify-between">
            <x-central.eyebrow>Recent Promo Codes</x-central.eyebrow>
            @if ($codes->isNotEmpty())
                <span class="text-[0.7rem] text-(--kn-muted)">Last {{ $codes->count() }} created</span>
            @endif
        </div>

        @if ($codes->isEmpty())
            <x-central.card padding="py-12 px-6" class="text-center">
                <x-heroicon-o-ticket class="mx-auto mb-3 block h-10 w-10 text-(--kn-muted)" />
                <div class="font-semibold text-(--kn-ink)">No promo codes yet</div>
                <div class="mt-1 text-[0.85rem] text-(--kn-muted)">Generated codes will appear here for reference.</div>
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
                                    ? $code->percent_off.'% off'
                                    : '$'.number_format(($code->amount_off_cents ?? 0) / 100, 2).' off';
                                $durationText = match ($code->duration) {
                                    'once' => 'Once',
                                    'repeating' => $code->duration_in_months.' months',
                                    'forever' => 'Forever',
                                    default => ucfirst($code->duration),
                                };
                                $isExpired = $code->expires_at && $code->expires_at->isPast();
                            @endphp
                            <x-central.tr>
                                <x-central.td>
                                    <span class="font-mono font-bold text-(--kn-honey-text)">{{ $code->code }}</span>
                                </x-central.td>
                                <x-central.td tone="white">{{ $discountText }}</x-central.td>
                                <x-central.td>{{ $durationText }}</x-central.td>
                                <x-central.td>
                                    @if ($code->tenant_id)
                                        <span class="font-mono text-[0.8rem] text-(--kn-ink)">{{ $code->tenant_id }}</span>
                                    @else
                                        <span class="text-(--kn-muted)">—</span>
                                    @endif
                                </x-central.td>
                                <x-central.td align="right" tone="white">{{ $code->max_redemptions }}</x-central.td>
                                <x-central.td>
                                    @if ($code->expires_at)
                                        <span @class([
                                            'text-[0.8rem]',
                                            'text-(--kn-danger)' => $isExpired,
                                            'text-(--kn-ink)' => ! $isExpired,
                                        ])>
                                            {{ $code->expires_at->format('M j, Y') }}
                                            @if ($isExpired)
                                                <span class="ml-1 text-[0.65rem] font-bold tracking-[0.08em] uppercase">Expired</span>
                                            @endif
                                        </span>
                                    @else
                                        <span class="text-(--kn-muted)">No expiry</span>
                                    @endif
                                </x-central.td>
                                <x-central.td>
                                    <div class="text-[0.8rem] text-(--kn-ink)">
                                        {{ $code->created_at?->format('M j, Y') ?? '—' }}
                                    </div>
                                    @if ($code->createdBy)
                                        <div class="text-[0.7rem] text-(--kn-muted)">
                                            by {{ $code->createdBy->name }}
                                        </div>
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
