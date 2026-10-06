<x-filament-panels::page>
    {{-- Hero Status Card --}}
    <x-central.card class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-5 {{ $maintenance_mode ? 'border-(--kn-danger)/30' : '' }}">
        <div class="flex items-center gap-4">
            @if ($maintenance_mode)
                <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-xl bg-(--kn-danger-tint)">
                    <x-heroicon-o-exclamation-triangle class="h-7 w-7 text-(--kn-danger)" />
                </div>
                <div>
                    <div class="mb-0.5 text-[0.65rem] font-bold tracking-[0.12em] text-(--kn-danger) uppercase">
                        System Status
                    </div>
                    <div class="text-[1.5rem] leading-tight font-bold text-(--kn-ink)">In Maintenance</div>
                    <div class="mt-1 text-[0.85rem] text-(--kn-muted)">
                        @if (! empty($affected_services))
                            {{ count($affected_services) }} {{ \Illuminate\Support\Str::plural('service', count($affected_services)) }} affected:
                            <span class="text-(--kn-ink)">{{ collect($affected_services)->map(fn ($s) => \Illuminate\Support\Str::headline($s))->join(', ') }}</span>
                        @else
                            No services selected — configure below
                        @endif
                    </div>
                </div>
            @else
                <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-xl bg-(--kn-success-tint)">
                    <x-heroicon-o-check-circle class="h-7 w-7 text-(--kn-success)" />
                </div>
                <div>
                    <div class="mb-0.5 text-[0.65rem] font-bold tracking-[0.12em] text-(--kn-success) uppercase">
                        System Status
                    </div>
                    <div class="text-[1.5rem] leading-tight font-bold text-(--kn-ink)">All Systems Online</div>
                    <div class="mt-1 text-[0.85rem] text-(--kn-muted)">Platform and all services running normally.</div>
                </div>
            @endif
        </div>

        <button
            type="button"
            @click="$dispatch('open-modal', 'confirm-maintenance')"
            class="shrink-0 inline-flex items-center justify-center gap-2 px-5 py-3 rounded-lg font-bold text-[0.85rem] border cursor-pointer transition-colors
                {{
                    $maintenance_mode
                    ? 'bg-(--kn-success-tint) text-(--kn-success) border-(--kn-success)/25 hover:bg-(--kn-success)/20'
                    : 'bg-(--kn-danger-tint) text-(--kn-danger) border-(--kn-danger)/25 hover:bg-(--kn-danger)/20'
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
                    <label for="maintenance-message" class="mb-2 block text-[0.85rem] font-semibold text-(--kn-ink)"
                        >Public message</label>
                    <x-central.textarea
                        wire:model.live="maintenance_message"
                        id="maintenance-message"
                        rows="3"
                        placeholder="We are currently performing scheduled maintenance. We'll be back shortly!"
                    />
                    <p class="mt-1.5 text-[0.75rem] text-(--kn-muted)">
                        Shown on the maintenance page to anyone hitting an affected service.
                    </p>
                </div>

                {{-- Schedule --}}
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label for="scheduled-start" class="mb-2 block text-[0.85rem] font-semibold text-(--kn-ink)"
                            >Scheduled start</label>
                        <x-central.input
                            type="datetime-local"
                            wire:model.live="maintenance_scheduled_start"
                            id="scheduled-start"
                        />
                        <p class="mt-1.5 text-[0.75rem] text-(--kn-muted)">Optional.</p>
                    </div>
                    <div>
                        <label for="scheduled-end" class="mb-2 block text-[0.85rem] font-semibold text-(--kn-ink)"
                            >Scheduled end</label>
                        <x-central.input
                            type="datetime-local"
                            wire:model.live="maintenance_scheduled_end"
                            id="scheduled-end"
                        />
                        <p class="mt-1.5 text-[0.75rem] text-(--kn-muted)">Shown in the preview as "expected back".</p>
                    </div>
                </div>

                {{-- Affected Services --}}
                <div>
                    <div class="mb-2 block text-[0.85rem] font-semibold text-(--kn-ink)">Affected services</div>
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
                                    {{ $checked ? 'border-(--kn-honey) bg-(--kn-surface-hover)' : 'border-(--kn-border) bg-(--kn-surface) hover:border-(--kn-honey)/30' }}"
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
                                        <x-heroicon-s-check-circle class="h-4 w-4 text-(--kn-honey-text)" />
                                    @else
                                        <div class="h-4 w-4 rounded-full border-2 border-(--kn-border)"></div>
                                    @endif
                                    <div class="text-[0.85rem] font-bold text-(--kn-ink)">{{ $service['label'] }}</div>
                                </div>
                                <div class="text-[0.7rem] leading-snug text-(--kn-muted)">{{ $service['desc'] }}</div>
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
                    <div class="inline-flex items-center gap-1.5 rounded-full border border-(--kn-danger)/25 bg-(--kn-danger-tint) px-2.5 py-1 text-[0.65rem] font-bold tracking-[0.1em] text-(--kn-danger) uppercase">
                        <span class="h-1.5 w-1.5 animate-pulse rounded-full bg-(--kn-danger)"></span>
                        In Maintenance
                    </div>
                @else
                    <div class="inline-flex items-center gap-1.5 rounded-full border border-(--kn-success)/25 bg-(--kn-success-tint) px-2.5 py-1 text-[0.65rem] font-bold tracking-[0.1em] text-(--kn-success) uppercase">
                        <span class="h-1.5 w-1.5 rounded-full bg-(--kn-success)"></span>
                        Live
                    </div>
                @endif
            </div>

            <p class="mb-3 text-[0.75rem] leading-relaxed text-(--kn-muted)">
                This is what visitors are seeing at <span class="font-mono text-(--kn-ink)">getkneadit.app</span> right
                now.
            </p>

            <div class="relative rounded-xl border {{ $maintenance_mode ? 'border-(--kn-danger)/25' : 'border-(--kn-success)/25' }} overflow-hidden bg-(--kn-surface)">
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
        <div class="text-[0.8rem] text-(--kn-muted)">Message, schedule, and affected services save together.</div>
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
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-(--kn-success-tint)">
                        <x-heroicon-o-arrow-uturn-up class="h-6 w-6 text-(--kn-success)" stroke-width="2" />
                    </div>
                    <div class="flex-1">
                        <div class="mb-1.5 text-[1.05rem] font-bold text-(--kn-ink)">Bring platform online?</div>
                        <div class="text-[0.85rem] leading-relaxed text-(--kn-ink)">
                            All affected services will become reachable again immediately. Customers, tenants, and API
                            callers will resume normal access.
                        </div>
                    </div>
                @else
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-(--kn-danger-tint)">
                        <x-heroicon-o-exclamation-triangle class="h-6 w-6 text-(--kn-danger)" stroke-width="2" />
                    </div>
                    <div class="flex-1">
                        <div class="mb-1.5 text-[1.05rem] font-bold text-(--kn-ink)">Enter maintenance mode?</div>
                        <div class="text-[0.85rem] leading-relaxed text-(--kn-ink)">
                            @if (! empty($affected_services))
                                <span class="font-semibold text-(--kn-ink)">{{ count($affected_services) }} {{ \Illuminate\Support\Str::plural('service', count($affected_services)) }}</span>
                                will show a maintenance page:
                                <span
                                    class="text-(--kn-honey-text)"
                                    >{{ collect($affected_services)->map(fn ($s) => \Illuminate\Support\Str::headline($s))->join(', ') }}</span
                                >. Active users will be disconnected.
                            @else
                                <span class="text-(--kn-danger)">No services are selected</span>
                                — toggling now won't actually take anything offline. Check at least one service under
                                "Affected services" first.
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <div class="flex items-center justify-end gap-2 border-t border-(--kn-border) bg-(--kn-surface-sunken) px-6 py-4">
            <button
                type="button"
                @click="open = false"
                class="cursor-pointer rounded-lg px-4 py-2 text-[0.85rem] font-semibold text-(--kn-ink) transition-colors hover:text-(--kn-ink)"
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
                        ? 'bg-(--kn-success) text-(--kn-on-danger) border-(--kn-success) hover:opacity-90'
                        : 'bg-(--kn-danger) text-(--kn-on-danger) border-(--kn-danger) hover:opacity-90'
                    }}"
            >
                {{ $maintenance_mode ? 'Bring Online' : 'Enter Maintenance' }}
            </button>
        </div>
    </x-central.modal>
</x-filament-panels::page>
