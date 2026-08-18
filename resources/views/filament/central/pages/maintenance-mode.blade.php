<x-filament-panels::page>
    {{-- Hero Status Card --}}
    <x-central.card class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-5 {{ $maintenance_mode ? 'border-red-500/30' : '' }}">
        <div class="flex items-center gap-4">
            @if ($maintenance_mode)
                <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-xl bg-red-500/15">
                    <x-heroicon-o-exclamation-triangle class="h-7 w-7 text-red-500" />
                </div>
                <div>
                    <div class="mb-0.5 text-[0.65rem] font-bold tracking-[0.12em] text-red-400 uppercase">
                        System Status
                    </div>
                    <div class="text-[1.5rem] leading-tight font-bold text-white">In Maintenance</div>
                    <div class="text-cinnamon mt-1 text-[0.85rem]">
                        @if (! empty($affected_services))
                            {{ count($affected_services) }} {{ \Illuminate\Support\Str::plural('service', count($affected_services)) }} affected:
                            <span class="text-parchment">{{ collect($affected_services)->map(fn ($s) => \Illuminate\Support\Str::headline($s))->join(', ') }}</span>
                        @else
                            No services selected — configure below
                        @endif
                    </div>
                </div>
            @else
                <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-xl bg-emerald-500/15">
                    <x-heroicon-o-check-circle class="h-7 w-7 text-emerald-500" />
                </div>
                <div>
                    <div class="mb-0.5 text-[0.65rem] font-bold tracking-[0.12em] text-emerald-400 uppercase">
                        System Status
                    </div>
                    <div class="text-[1.5rem] leading-tight font-bold text-white">All Systems Online</div>
                    <div class="text-cinnamon mt-1 text-[0.85rem]">Platform and all services running normally.</div>
                </div>
            @endif
        </div>

        <button
            type="button"
            @click="$dispatch('open-modal', 'confirm-maintenance')"
            class="shrink-0 inline-flex items-center justify-center gap-2 px-5 py-3 rounded-lg font-bold text-[0.85rem] border cursor-pointer transition-colors
                {{
                    $maintenance_mode
                    ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/25 hover:bg-emerald-500/20'
                    : 'bg-red-500/10 text-red-400 border-red-500/25 hover:bg-red-500/20'
                }}"
        >
            @if ($maintenance_mode)
                <x-heroicon-o-arrow-uturn-up class="h-4 w-4" stroke-width="2.5" />
                Bring Online
            @else
                <x-heroicon-o-power class="h-4 w-4" stroke-width="2.5" />
                Enter Maintenance
            @endif
        </button>
    </x-central.card>

    {{-- Settings + Preview --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-[1.25fr_1fr]">
        {{-- Settings --}}
        <x-central.card>
            <x-central.eyebrow class="mb-5">Configuration</x-central.eyebrow>

            <div class="space-y-5">
                {{-- Public Message --}}
                <div>
                    <label for="maintenance-message" class="mb-2 block text-[0.85rem] font-semibold text-white"
                        >Public message</label>
                    <x-central.textarea
                        wire:model.live="maintenance_message"
                        id="maintenance-message"
                        rows="3"
                        placeholder="We are currently performing scheduled maintenance. We'll be back shortly!"
                    />
                    <p class="text-cinnamon mt-1.5 text-[0.75rem]">
                        Shown on the maintenance page to anyone hitting an affected service.
                    </p>
                </div>

                {{-- Schedule --}}
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label for="scheduled-start" class="mb-2 block text-[0.85rem] font-semibold text-white"
                            >Scheduled start</label>
                        <x-central.input
                            type="datetime-local"
                            wire:model.live="maintenance_scheduled_start"
                            id="scheduled-start"
                        />
                        <p class="text-cinnamon mt-1.5 text-[0.75rem]">Optional.</p>
                    </div>
                    <div>
                        <label for="scheduled-end" class="mb-2 block text-[0.85rem] font-semibold text-white"
                            >Scheduled end</label>
                        <x-central.input
                            type="datetime-local"
                            wire:model.live="maintenance_scheduled_end"
                            id="scheduled-end"
                        />
                        <p class="text-cinnamon mt-1.5 text-[0.75rem]">Shown in the preview as "expected back".</p>
                    </div>
                </div>

                {{-- Affected Services --}}
                <div>
                    <div class="mb-2 block text-[0.85rem] font-semibold text-white">Affected services</div>
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                        @foreach ([
                            'storefront' => ['label' => 'Storefront', 'desc' => 'Customer-facing bakery sites'],
                            'admin' => ['label' => 'Admin Panel', 'desc' => 'Tenant /admin area'],
                            'api' => ['label' => 'API', 'desc' => 'Public API endpoints'],
                        ] as $key => $service)
                            @php $checked = in_array($key, $affected_services, true); @endphp
                            <label
                                for="svc-{{ $key }}"
                                class="cursor-pointer rounded-lg border p-3.5 transition-colors
                                    {{ $checked ? 'border-honey bg-honey/8' : 'border-honey/12 bg-warm-black hover:border-honey/30' }}"
                            >
                                <input
                                    type="checkbox"
                                    wire:model.live="affected_services"
                                    value="{{ $key }}"
                                    id="svc-{{ $key }}"
                                    class="sr-only"
                                />
                                <div class="mb-1 flex items-center gap-2">
                                    @if ($checked)
                                        <x-heroicon-s-check-circle class="text-honey h-4 w-4" />
                                    @else
                                        <div class="border-cinnamon/40 h-4 w-4 rounded-full border-2"></div>
                                    @endif
                                    <div class="text-[0.85rem] font-bold text-white">{{ $service['label'] }}</div>
                                </div>
                                <div class="text-cinnamon text-[0.7rem] leading-snug">{{ $service['desc'] }}</div>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>
        </x-central.card>

        {{-- Live Preview --}}
        <x-central.card>
            <div class="mb-5 flex items-center justify-between">
                <x-central.eyebrow>Preview</x-central.eyebrow>
                @if ($maintenance_mode)
                    <div class="inline-flex items-center gap-1.5 rounded-full border border-red-500/25 bg-red-500/15 px-2.5 py-1 text-[0.65rem] font-bold tracking-[0.1em] text-red-400 uppercase">
                        <span class="h-1.5 w-1.5 animate-pulse rounded-full bg-red-500"></span>
                        In Maintenance
                    </div>
                @else
                    <div class="inline-flex items-center gap-1.5 rounded-full border border-emerald-500/25 bg-emerald-500/15 px-2.5 py-1 text-[0.65rem] font-bold tracking-[0.1em] text-emerald-400 uppercase">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                        Live
                    </div>
                @endif
            </div>

            <p class="text-cinnamon mb-3 text-[0.75rem] leading-relaxed">
                This is what visitors are seeing at <span class="text-parchment font-mono">getkneadit.app</span> right
                now.
            </p>

            <div class="relative rounded-xl border {{ $maintenance_mode ? 'border-red-500/25' : 'border-emerald-500/25' }} overflow-hidden bg-warm-black">
                @if ($maintenance_mode)
                    <iframe
                        src="{{ route('central.maintenance-mode.preview') }}?{{
                            http_build_query(array_filter([
                                'message' => $maintenance_message,
                                'end' => $maintenance_scheduled_end,
                            ]))
                        }}"
                        title="Maintenance page preview"
                        class="block h-[480px] w-full border-0"
                        loading="lazy"
                    ></iframe>
                @else
                    <iframe
                        src="{{ route('home') }}"
                        title="Landing page preview"
                        class="block h-[480px] w-full border-0"
                        loading="lazy"
                    ></iframe>
                @endif
            </div>
        </x-central.card>
    </div>

    {{-- Save Bar --}}
    <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-end">
        <div class="text-cinnamon text-[0.8rem]">Message, schedule, and affected services save together.</div>
        <x-central.button wire:click="save" class="gap-1.5 whitespace-nowrap">
            <x-heroicon-o-check class="h-4 w-4" stroke-width="2.5" />
            Save Settings
        </x-central.button>
    </div>

    {{-- Confirm Modal --}}
    <x-central.modal name="confirm-maintenance" variant="{{ $maintenance_mode ? 'success' : 'danger' }}">
        <div class="p-6">
            <div class="flex items-start gap-4">
                @if ($maintenance_mode)
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-emerald-500/15">
                        <x-heroicon-o-arrow-uturn-up class="h-6 w-6 text-emerald-500" stroke-width="2" />
                    </div>
                    <div class="flex-1">
                        <div class="mb-1.5 text-[1.05rem] font-bold text-white">Bring platform online?</div>
                        <div class="text-parchment text-[0.85rem] leading-relaxed">
                            All affected services will become reachable again immediately. Customers, tenants, and API
                            callers will resume normal access.
                        </div>
                    </div>
                @else
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-red-500/15">
                        <x-heroicon-o-exclamation-triangle class="h-6 w-6 text-red-500" stroke-width="2" />
                    </div>
                    <div class="flex-1">
                        <div class="mb-1.5 text-[1.05rem] font-bold text-white">Enter maintenance mode?</div>
                        <div class="text-parchment text-[0.85rem] leading-relaxed">
                            @if (! empty($affected_services))
                                <span class="font-semibold text-white">{{ count($affected_services) }} {{ \Illuminate\Support\Str::plural('service', count($affected_services)) }}</span>
                                will show a maintenance page:
                                <span
                                    class="text-honey"
                                    >{{ collect($affected_services)->map(fn ($s) => \Illuminate\Support\Str::headline($s))->join(', ') }}</span
                                >. Active users will be disconnected.
                            @else
                                <span class="text-red-400">No services are selected</span>
                                — toggling now won't actually take anything offline. Check at least one service under
                                "Affected services" first.
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <div class="bg-espresso/50 border-honey/8 flex items-center justify-end gap-2 border-t px-6 py-4">
            <button
                type="button"
                @click="open = false"
                class="text-parchment cursor-pointer rounded-lg px-4 py-2 text-[0.85rem] font-semibold transition-colors hover:text-white"
            >
                Cancel
            </button>
            <button
                type="button"
                @click="
                    open = false;
                    $wire.toggleMaintenance();
                "
                class="px-4 py-2 rounded-lg text-[0.85rem] font-bold border cursor-pointer transition-colors
                    {{
                        $maintenance_mode
                        ? 'bg-emerald-500 text-white border-emerald-500 hover:bg-emerald-400'
                        : 'bg-red-500 text-white border-red-500 hover:bg-red-400'
                    }}"
            >
                {{ $maintenance_mode ? 'Bring Online' : 'Enter Maintenance' }}
            </button>
        </div>
    </x-central.modal>
</x-filament-panels::page>
