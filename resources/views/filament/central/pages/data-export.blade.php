<x-filament-panels::page>
    {{-- Tenant Selector — full width bar --}}
    <div style="background: #1c1410; border: 1px solid rgba(212,146,12,0.12); border-radius: 12px; padding: 1.25rem 1.5rem; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 1rem;">
        <label for="tenant-select" style="color: #d4920c; font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.1em; font-weight: 600; white-space: nowrap;">
            Select Tenant
        </label>
        <select
            wire:model.live="selectedTenant"
            id="tenant-select"
            style="flex: 1; max-width: 400px; padding: 0.5rem 2rem 0.5rem 0.75rem; background: #2a1f18; border: 1px solid rgba(212,146,12,0.12); border-radius: 8px; color: #faf0d6; font-size: 0.875rem; outline: none; -webkit-appearance: none; appearance: none; background-image: url('data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%2212%22 height=%2212%22 viewBox=%220 0 24 24%22 fill=%22none%22 stroke=%22%23d4920c%22 stroke-width=%222%22><path d=%22M6 9l6 6 6-6%22/></svg>'); background-repeat: no-repeat; background-position: right 0.75rem center;"
        >
            <option value="">— Choose a bakery —</option>
            @foreach ($this->getTenants() as $id => $name)
                <option value="{{ $id }}">{{ $name }}</option>
            @endforeach
        </select>
    </div>

    @if ($selectedTenant)
        {{-- Export Grid — 3 columns --}}
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; margin-bottom: 1rem;">
            @foreach ($this->getExportTypes() as $type => $info)
                <div style="background: #1c1410; border: 1px solid rgba(212,146,12,0.12); border-radius: 12px; padding: 1.25rem; display: flex; flex-direction: column; justify-content: space-between; min-height: 160px;">
                    <div>
                        <div style="color: white; font-weight: 700; font-size: 0.95rem; margin-bottom: 0.35rem;">{{ $info['name'] }}</div>
                        <div style="color: #8b6844; font-size: 0.8rem; line-height: 1.4; margin-bottom: 0.75rem;">{{ $info['description'] }}</div>
                        @if (isset($counts[$type]))
                            <div style="color: #d4920c; font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.1em; font-weight: 600;">
                                {{ number_format($counts[$type]) }} rows
                            </div>
                        @endif
                    </div>
                    <a
                        href="{{ route('central.export', ['tenant' => $selectedTenant, 'type' => $type]) }}"
                        style="display: inline-flex; align-items: center; justify-content: center; gap: 0.4rem; padding: 0.5rem 0.75rem; background: rgba(212,146,12,0.1); color: #e8b04a; font-weight: 600; font-size: 0.8rem; border: 1px solid rgba(212,146,12,0.2); border-radius: 8px; text-decoration: none; margin-top: 0.75rem;"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width: 14px; height: 14px;">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                        </svg>
                        Export CSV
                    </a>
                </div>
            @endforeach
        </div>

        {{-- Download All — full width accent bar --}}
        <div style="background: #1c1410; border: 2px solid rgba(212,146,12,0.25); border-radius: 12px; padding: 1.25rem 1.5rem; display: flex; align-items: center; justify-content: space-between;">
            <div>
                <div style="color: white; font-weight: 700; font-size: 0.95rem;">Download All Data</div>
                <div style="color: #8b6844; font-size: 0.8rem;">All exports bundled into a single ZIP archive.</div>
            </div>
            <a
                href="{{ route('central.export', ['tenant' => $selectedTenant, 'type' => 'all']) }}"
                style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.6rem 1.25rem; background: #d4920c; color: #1c1410; font-weight: 700; font-size: 0.85rem; border-radius: 8px; text-decoration: none; white-space: nowrap;"
            >
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width: 16px; height: 16px;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                </svg>
                Download ZIP
            </a>
        </div>
    @else
        <div style="text-align: center; padding: 4rem 1.5rem; background: #1c1410; border: 1px solid rgba(212,146,12,0.12); border-radius: 12px;">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="rgba(212,146,12,0.3)" style="width: 48px; height: 48px; margin: 0 auto 1rem; display: block;">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
            </svg>
            <div style="color: #8b6844; font-size: 0.9rem;">Select a bakery above to view export options.</div>
        </div>
    @endif
</x-filament-panels::page>
