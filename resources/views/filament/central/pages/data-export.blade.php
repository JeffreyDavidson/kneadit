<x-filament-panels::page>
    {{-- Tenant Selector — full width bar --}}
    <x-central.card padding="px-6 py-5" class="mb-6">
        <div class="flex items-center gap-4 flex-wrap">
            <div class="flex items-center gap-3 flex-1 min-w-[280px]">
                <div class="w-9 h-9 rounded-xl bg-honey/15 border border-honey/25 flex items-center justify-center shrink-0">
                    <x-heroicon-o-building-storefront class="w-4 h-4 text-honey" />
                </div>
                <div class="flex-1">
                    <label for="tenant-select" class="block text-cinnamon text-[0.7rem] uppercase tracking-[0.08em] font-semibold mb-1">Select Bakery</label>
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
                    <div class="text-cinnamon text-[0.7rem] uppercase tracking-[0.08em] font-semibold">Total Rows</div>
                    <div class="text-white font-bold text-[1.35rem] leading-none mt-1">{{ number_format(array_sum($counts)) }}</div>
                </div>
            @endif
        </div>
    </x-central.card>

    @if ($selectedTenant)
        {{-- Export Grid — 3 columns --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-4">
            @foreach ($this->getExportTypes() as $type => $info)
                @php
                    $count = $counts[$type] ?? null;
                    $isEmpty = $count === 0;
                @endphp
                <x-central.card padding="p-5" class="flex flex-col">
                    <div class="flex items-start gap-3 mb-3">
                        <div class="shrink-0 w-10 h-10 rounded-xl bg-honey/10 border border-honey/20 flex items-center justify-center">
                            <x-filament::icon :icon="$info['icon']" class="w-5 h-5 text-honey" />
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-baseline gap-2 flex-wrap">
                                <span class="text-white font-bold text-[0.95rem]">{{ $info['name'] }}</span>
                                @if ($count !== null)
                                    <span @class([
                                        'text-[0.75rem] font-semibold tabular-nums',
                                        'text-honey' => ! $isEmpty,
                                        'text-cinnamon' => $isEmpty,
                                    ])>
                                        {{ number_format($count) }} {{ Illuminate\Support\Str::plural('row', $count) }}
                                    </span>
                                @endif
                            </div>
                            <div class="text-cinnamon text-[0.8rem] leading-snug mt-1">{{ $info['description'] }}</div>
                        </div>
                    </div>

                    @if ($isEmpty)
                        <button type="button" disabled
                            class="inline-flex items-center justify-center gap-1.5 px-3 py-2 bg-espresso text-cinnamon font-semibold text-[0.8rem] border border-honey/10 rounded-lg mt-auto cursor-not-allowed">
                            <x-heroicon-o-no-symbol class="w-3.5 h-3.5" />
                            Nothing to export
                        </button>
                    @else
                        <a href="{{ route('central.export', ['tenant' => $selectedTenant, 'type' => $type]) }}"
                           class="inline-flex items-center justify-center gap-1.5 px-3 py-2 bg-honey/10 text-honey hover:bg-honey hover:text-warm-black font-semibold text-[0.8rem] border border-honey/25 rounded-lg no-underline mt-auto transition-colors">
                            <x-heroicon-o-arrow-down-tray class="w-3.5 h-3.5" />
                            Export CSV
                        </a>
                    @endif
                </x-central.card>
            @endforeach
        </div>

        {{-- Download All — full width accent bar --}}
        <x-central.card padding="px-6 py-5" class="bg-honey/5 border-honey/25 flex items-center justify-between gap-4 flex-wrap">
            <div class="flex items-center gap-3 flex-1 min-w-[240px]">
                <div class="w-10 h-10 rounded-xl bg-honey/15 border border-honey/25 flex items-center justify-center shrink-0">
                    <x-heroicon-o-archive-box class="w-5 h-5 text-honey" />
                </div>
                <div>
                    <div class="text-white font-bold text-[0.95rem]">Download All Data</div>
                    <div class="text-cinnamon text-[0.8rem]">All {{ count($this->getExportTypes()) }} exports bundled into a single ZIP archive.</div>
                </div>
            </div>
            <x-central.button :href="route('central.export', ['tenant' => $selectedTenant, 'type' => 'all'])" class="gap-2 whitespace-nowrap">
                <x-heroicon-o-arrow-down-tray class="w-4 h-4" />
                Download ZIP
            </x-central.button>
        </x-central.card>
    @else
        <x-central.card padding="py-16 px-6" class="text-center">
            <x-heroicon-o-arrow-down-tray class="w-12 h-12 mx-auto mb-4 block text-honey/30" />
            <div class="text-white font-semibold text-[1rem]">Pick a bakery to start</div>
            <div class="text-cinnamon text-[0.85rem] mt-2 max-w-[420px] mx-auto">
                Choose a bakery above to see exportable data — products, categories, orders, customers, and reviews — plus a one-click ZIP of everything.
            </div>
        </x-central.card>
    @endif
</x-filament-panels::page>
