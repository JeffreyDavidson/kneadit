<x-filament-panels::page>
    {{-- Tenant Selector — full width bar --}}
    <x-central.card padding="px-6 py-5" class="mb-6 flex items-center gap-4">
        <x-central.eyebrow as="label" for="tenant-select" class="whitespace-nowrap">Select Tenant</x-central.eyebrow>
        <x-central.select wire:model.live="selectedTenant" id="tenant-select" class="flex-1 max-w-[400px]">
            <option value="">— Choose a bakery —</option>
            @foreach ($this->getTenants() as $id => $name)
                <option value="{{ $id }}">{{ $name }}</option>
            @endforeach
        </x-central.select>
    </x-central.card>

    @if ($selectedTenant)
        {{-- Export Grid — 3 columns --}}
        <div class="grid grid-cols-3 gap-4 mb-4">
            @foreach ($this->getExportTypes() as $type => $info)
                <x-central.card padding="p-5" class="flex flex-col justify-between min-h-[160px]">
                    <div>
                        <div class="text-white font-bold text-[0.95rem] mb-1.5">{{ $info['name'] }}</div>
                        <div class="text-cinnamon text-[0.8rem] leading-snug mb-3">{{ $info['description'] }}</div>
                        @if (isset($counts[$type]))
                            <x-central.eyebrow>{{ number_format($counts[$type]) }} rows</x-central.eyebrow>
                        @endif
                    </div>
                    <a href="{{ route('central.export', ['tenant' => $selectedTenant, 'type' => $type]) }}"
                       class="inline-flex items-center justify-center gap-1.5 px-3 py-2 bg-honey/10 text-golden font-semibold text-[0.8rem] border border-honey/20 rounded-lg no-underline mt-3">
                        <x-heroicon-o-arrow-down-tray class="w-3.5 h-3.5" />
                        Export CSV
                    </a>
                </x-central.card>
            @endforeach
        </div>

        {{-- Download All — full width accent bar --}}
        <x-central.card padding="px-6 py-5" class="border-2 border-honey/25 flex items-center justify-between">
            <div>
                <div class="text-white font-bold text-[0.95rem]">Download All Data</div>
                <div class="text-cinnamon text-[0.8rem]">All exports bundled into a single ZIP archive.</div>
            </div>
            <x-central.button :href="route('central.export', ['tenant' => $selectedTenant, 'type' => 'all'])" class="gap-2 whitespace-nowrap">
                <x-heroicon-o-arrow-down-tray class="w-4 h-4" />
                Download ZIP
            </x-central.button>
        </x-central.card>
    @else
        <x-central.card padding="py-16 px-6" class="text-center">
            <x-heroicon-o-arrow-down-tray class="w-12 h-12 mx-auto mb-4 block text-honey/30" />
            <div class="text-cinnamon text-[0.9rem]">Select a bakery above to view export options.</div>
        </x-central.card>
    @endif
</x-filament-panels::page>
