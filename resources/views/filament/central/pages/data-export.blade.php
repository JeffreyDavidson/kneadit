<x-filament-panels::page>
    {{-- Tenant Selector — full width bar --}}
    <x-central.card padding="px-6 py-5" class="mb-6">
        <div class="flex flex-wrap items-center gap-4">
            <div class="flex min-w-[280px] flex-1 items-center gap-3">
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl border border-(--kn-honey)/25 bg-(--kn-warning-tint)">
                    <x-heroicon-o-building-storefront class="h-4 w-4 text-(--kn-honey-text)" />
                </div>
                <div class="flex-1">
                    <label
                        for="tenant-select"
                        class="mb-1 block text-[0.7rem] font-semibold tracking-[0.08em] text-(--kn-muted) uppercase"
                    >Select Bakery</label>
                    <x-central.select wire:model.live="selectedTenant" id="tenant-select" class="w-full max-w-[400px]">
                        <option value="">— Choose a bakery —</option>
                        @foreach ($this->getTenants() as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </x-central.select>
                </div>
            </div>
            @if ($selectedTenant && ! empty($counts))
                <div class="text-right">
                    <div class="text-[0.7rem] font-semibold tracking-[0.08em] text-(--kn-muted) uppercase">
                        Total Rows
                    </div>
                    <div class="mt-1 text-[1.35rem] leading-none font-bold text-(--kn-ink)">
                        {{ number_format(array_sum($counts)) }}
                    </div>
                </div>
            @endif
        </div>
    </x-central.card>

    @if ($selectedTenant)
        {{-- Export Grid — 3 columns --}}
        <div class="mb-4 grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
            @foreach ($this->getExportTypes() as $type => $info)
                @php
                    $count = $counts[$type] ?? null;
                    $isEmpty = $count === 0;
                @endphp
                <x-central.card padding="p-5" class="flex flex-col">
                    <div class="mb-3 flex items-start gap-3">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-(--kn-border) bg-(--kn-warning-tint)">
                            <x-filament::icon :icon="$info['icon']" class="h-5 w-5 text-(--kn-honey-text)" />
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-baseline gap-2">
                                <span class="text-[0.95rem] font-bold text-(--kn-ink)">{{ $info['name'] }}</span>
                                @if ($count !== null)
                                    <span @class([
                                        'text-[0.75rem] font-semibold tabular-nums',
                                        'text-(--kn-honey-text)' => ! $isEmpty,
                                        'text-(--kn-muted)' => $isEmpty,
                                    ])>
                                        {{ number_format($count) }} {{ Illuminate\Support\Str::plural('row', $count) }}
                                    </span>
                                @endif
                            </div>
                            <div class="mt-1 text-[0.8rem] leading-snug text-(--kn-muted)">
                                {{ $info['description'] }}
                            </div>
                        </div>
                    </div>

                    @if ($isEmpty)
                        <button
                            type="button"
                            disabled
                            class="mt-auto inline-flex cursor-not-allowed items-center justify-center gap-1.5 rounded-lg border border-(--kn-border) bg-(--kn-surface-sunken) px-3 py-2 text-[0.8rem] font-semibold text-(--kn-muted)"
                        >
                            <x-heroicon-o-no-symbol class="h-3.5 w-3.5" />
                            Nothing to export
                        </button>
                    @else
                        <a
                            href="{{ route('central.export', ['tenant' => $selectedTenant, 'type' => $type]) }}"
                            class="mt-auto inline-flex items-center justify-center gap-1.5 rounded-lg border border-(--kn-honey)/25 bg-(--kn-warning-tint) px-3 py-2 text-[0.8rem] font-semibold text-(--kn-honey-text) no-underline transition-colors hover:bg-(--kn-honey) hover:text-(--kn-on-honey)"
                        >
                            <x-heroicon-o-arrow-down-tray class="h-3.5 w-3.5" />
                            Export CSV
                        </a>
                    @endif
                </x-central.card>
            @endforeach
        </div>

        {{-- Download All — full width accent bar --}}
        <x-central.card
            padding="px-6 py-5"
            class="flex flex-wrap items-center justify-between gap-4 border-(--kn-honey)/25 bg-(--kn-surface-hover)"
        >
            <div class="flex min-w-[240px] flex-1 items-center gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-(--kn-honey)/25 bg-(--kn-warning-tint)">
                    <x-heroicon-o-archive-box class="h-5 w-5 text-(--kn-honey-text)" />
                </div>
                <div>
                    <div class="text-[0.95rem] font-bold text-(--kn-ink)">Download All Data</div>
                    <div class="text-[0.8rem] text-(--kn-muted)">
                        All {{ count($this->getExportTypes()) }} exports bundled into a single ZIP archive.
                    </div>
                </div>
            </div>
            <x-central.button
                :href="route('central.export', ['tenant' => $selectedTenant, 'type' => 'all'])"
                class="gap-2 whitespace-nowrap"
            >
                <x-heroicon-o-arrow-down-tray class="h-4 w-4" />
                Download ZIP
            </x-central.button>
        </x-central.card>
    @else
        <x-central.card padding="py-16 px-6" class="text-center">
            <x-heroicon-o-arrow-down-tray class="mx-auto mb-4 block h-12 w-12 text-(--kn-muted)" />
            <div class="text-[1rem] font-semibold text-(--kn-ink)">Pick a bakery to start</div>
            <div class="mx-auto mt-2 max-w-[420px] text-[0.85rem] text-(--kn-muted)">
                Choose a bakery above to see exportable data — products, categories, orders, customers, and reviews —
                plus a one-click ZIP of everything.
            </div>
        </x-central.card>
    @endif
</x-filament-panels::page>
