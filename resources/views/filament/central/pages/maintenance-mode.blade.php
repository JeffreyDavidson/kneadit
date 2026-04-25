<x-filament-panels::page>
    {{-- Hero Status Card --}}
    <x-central.card class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-5 {{ $maintenance_mode ? 'border-red-500/30' : '' }}">
        <div class="flex items-center gap-4">
            @if ($maintenance_mode)
                <div class="shrink-0 w-14 h-14 rounded-xl bg-red-500/15 flex items-center justify-center">
                    <x-heroicon-o-exclamation-triangle class="w-7 h-7 text-red-500" />
                </div>
                <div>
                    <div class="text-[0.65rem] uppercase tracking-[0.12em] text-red-400 font-bold mb-0.5">System Status</div>
                    <div class="text-white text-[1.5rem] font-bold leading-tight">In Maintenance</div>
                    <div class="text-cinnamon text-[0.85rem] mt-1">
                        @if (! empty($affected_services))
                            {{ count($affected_services) }} {{ \Illuminate\Support\Str::plural('service', count($affected_services)) }} affected:
                            <span class="text-parchment">{{ collect($affected_services)->map(fn ($s) => \Illuminate\Support\Str::headline($s))->join(', ') }}</span>
                        @else
                            No services selected — configure below
                        @endif
                    </div>
                </div>
            @else
                <div class="shrink-0 w-14 h-14 rounded-xl bg-emerald-500/15 flex items-center justify-center">
                    <x-heroicon-o-check-circle class="w-7 h-7 text-emerald-500" />
                </div>
                <div>
                    <div class="text-[0.65rem] uppercase tracking-[0.12em] text-emerald-400 font-bold mb-0.5">System Status</div>
                    <div class="text-white text-[1.5rem] font-bold leading-tight">All Systems Online</div>
                    <div class="text-cinnamon text-[0.85rem] mt-1">Platform and all services running normally.</div>
                </div>
            @endif
        </div>

        <button type="button" @click="$dispatch('open-modal', 'confirm-maintenance')"
            class="shrink-0 inline-flex items-center justify-center gap-2 px-5 py-3 rounded-lg font-bold text-[0.85rem] border cursor-pointer transition-colors
                {{ $maintenance_mode
                    ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/25 hover:bg-emerald-500/20'
                    : 'bg-red-500/10 text-red-400 border-red-500/25 hover:bg-red-500/20' }}">
            @if ($maintenance_mode)
                <x-heroicon-o-arrow-uturn-up class="w-4 h-4" stroke-width="2.5" />
                Bring Online
            @else
                <x-heroicon-o-power class="w-4 h-4" stroke-width="2.5" />
                Enter Maintenance
            @endif
        </button>
    </x-central.card>

    {{-- Settings + Preview --}}
    <div class="grid grid-cols-1 lg:grid-cols-[1.25fr_1fr] gap-6">
        {{-- Settings --}}
        <x-central.card>
            <x-central.eyebrow class="mb-5">Configuration</x-central.eyebrow>

            <div class="space-y-5">
                {{-- Public Message --}}
                <div>
                    <label for="maintenance-message" class="block text-white text-[0.85rem] font-semibold mb-2">Public message</label>
                    <x-central.textarea wire:model.live="maintenance_message" id="maintenance-message" rows="3"
                        placeholder="We are currently performing scheduled maintenance. We'll be back shortly!" />
                    <p class="text-cinnamon text-[0.75rem] mt-1.5">Shown on the maintenance page to anyone hitting an affected service.</p>
                </div>

                {{-- Schedule --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="scheduled-start" class="block text-white text-[0.85rem] font-semibold mb-2">Scheduled start</label>
                        <x-central.input type="datetime-local" wire:model.live="maintenance_scheduled_start" id="scheduled-start" />
                        <p class="text-cinnamon text-[0.75rem] mt-1.5">Optional.</p>
                    </div>
                    <div>
                        <label for="scheduled-end" class="block text-white text-[0.85rem] font-semibold mb-2">Scheduled end</label>
                        <x-central.input type="datetime-local" wire:model.live="maintenance_scheduled_end" id="scheduled-end" />
                        <p class="text-cinnamon text-[0.75rem] mt-1.5">Shown in the preview as "expected back".</p>
                    </div>
                </div>

                {{-- Affected Services --}}
                <div>
                    <div class="block text-white text-[0.85rem] font-semibold mb-2">Affected services</div>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        @foreach ([
                            'storefront' => ['label' => 'Storefront', 'desc' => 'Customer-facing bakery sites'],
                            'admin' => ['label' => 'Admin Panel', 'desc' => 'Tenant /admin area'],
                            'api' => ['label' => 'API', 'desc' => 'Public API endpoints'],
                        ] as $key => $service)
                            @php $checked = in_array($key, $affected_services, true); @endphp
                            <label for="svc-{{ $key }}"
                                class="cursor-pointer rounded-lg border p-3.5 transition-colors
                                    {{ $checked ? 'border-honey bg-honey/8' : 'border-honey/12 bg-warm-black hover:border-honey/30' }}">
                                <input type="checkbox" wire:model.live="affected_services" value="{{ $key }}" id="svc-{{ $key }}" class="sr-only">
                                <div class="flex items-center gap-2 mb-1">
                                    @if ($checked)
                                        <x-heroicon-s-check-circle class="w-4 h-4 text-honey" />
                                    @else
                                        <div class="w-4 h-4 rounded-full border-2 border-cinnamon/40"></div>
                                    @endif
                                    <div class="text-white text-[0.85rem] font-bold">{{ $service['label'] }}</div>
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
            <div class="flex items-center justify-between mb-5">
                <x-central.eyebrow>Preview</x-central.eyebrow>
                @if ($maintenance_mode)
                    <div class="inline-flex items-center gap-1.5 bg-red-500/15 text-red-400 text-[0.65rem] font-bold uppercase tracking-[0.1em] border border-red-500/25 rounded-full px-2.5 py-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-red-500 animate-pulse"></span>
                        In Maintenance
                    </div>
                @else
                    <div class="inline-flex items-center gap-1.5 bg-emerald-500/15 text-emerald-400 text-[0.65rem] font-bold uppercase tracking-[0.1em] border border-emerald-500/25 rounded-full px-2.5 py-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                        Live
                    </div>
                @endif
            </div>

            <p class="text-cinnamon text-[0.75rem] mb-3 leading-relaxed">
                This is what visitors are seeing at <span class="text-parchment font-mono">getkneadit.app</span> right now.
            </p>

            <div class="relative rounded-xl border {{ $maintenance_mode ? 'border-red-500/25' : 'border-emerald-500/25' }} overflow-hidden bg-warm-black">
                @if ($maintenance_mode)
                    <iframe
                        src="{{ route('central.maintenance-mode.preview') }}?{{ http_build_query(array_filter([
                            'message' => $maintenance_message,
                            'end' => $maintenance_scheduled_end,
                        ])) }}"
                        title="Maintenance page preview"
                        class="w-full h-[480px] border-0 block"
                        loading="lazy"
                    ></iframe>
                @else
                    <iframe
                        src="{{ route('home') }}"
                        title="Landing page preview"
                        class="w-full h-[480px] border-0 block"
                        loading="lazy"
                    ></iframe>
                @endif
            </div>
        </x-central.card>
    </div>

    {{-- Save Bar --}}
    <div class="mt-6 flex flex-col sm:flex-row sm:items-center sm:justify-end gap-3">
        <div class="text-cinnamon text-[0.8rem]">Message, schedule, and affected services save together.</div>
        <x-central.button wire:click="save" class="gap-1.5 whitespace-nowrap">
            <x-heroicon-o-check class="w-4 h-4" stroke-width="2.5" />
            Save Settings
        </x-central.button>
    </div>

    {{-- Confirm Modal --}}
    <x-central.modal name="confirm-maintenance" variant="{{ $maintenance_mode ? 'success' : 'danger' }}">
        <div class="p-6">
            <div class="flex items-start gap-4">
                @if ($maintenance_mode)
                    <div class="shrink-0 w-12 h-12 rounded-xl bg-emerald-500/15 flex items-center justify-center">
                        <x-heroicon-o-arrow-uturn-up class="w-6 h-6 text-emerald-500" stroke-width="2" />
                    </div>
                    <div class="flex-1">
                        <div class="text-white text-[1.05rem] font-bold mb-1.5">Bring platform online?</div>
                        <div class="text-parchment text-[0.85rem] leading-relaxed">
                            All affected services will become reachable again immediately. Customers, tenants, and API callers will resume normal access.
                        </div>
                    </div>
                @else
                    <div class="shrink-0 w-12 h-12 rounded-xl bg-red-500/15 flex items-center justify-center">
                        <x-heroicon-o-exclamation-triangle class="w-6 h-6 text-red-500" stroke-width="2" />
                    </div>
                    <div class="flex-1">
                        <div class="text-white text-[1.05rem] font-bold mb-1.5">Enter maintenance mode?</div>
                        <div class="text-parchment text-[0.85rem] leading-relaxed">
                            @if (! empty($affected_services))
                                <span class="text-white font-semibold">{{ count($affected_services) }} {{ \Illuminate\Support\Str::plural('service', count($affected_services)) }}</span> will show a maintenance page:
                                <span class="text-honey">{{ collect($affected_services)->map(fn ($s) => \Illuminate\Support\Str::headline($s))->join(', ') }}</span>.
                                Active users will be disconnected.
                            @else
                                <span class="text-red-400">No services are selected</span> — toggling now won't actually take anything offline. Check at least one service under "Affected services" first.
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <div class="flex items-center justify-end gap-2 px-6 py-4 bg-espresso/50 border-t border-honey/8">
            <button type="button" @click="open = false"
                class="px-4 py-2 rounded-lg text-[0.85rem] font-semibold text-parchment hover:text-white transition-colors cursor-pointer">
                Cancel
            </button>
            <button type="button"
                @click="open = false; $wire.toggleMaintenance()"
                class="px-4 py-2 rounded-lg text-[0.85rem] font-bold border cursor-pointer transition-colors
                    {{ $maintenance_mode
                        ? 'bg-emerald-500 text-white border-emerald-500 hover:bg-emerald-400'
                        : 'bg-red-500 text-white border-red-500 hover:bg-red-400' }}">
                {{ $maintenance_mode ? 'Bring Online' : 'Enter Maintenance' }}
            </button>
        </div>
    </x-central.modal>
</x-filament-panels::page>
