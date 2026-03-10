<x-filament-panels::page>
    <div style="max-width: 1200px; margin: 0 auto;">
        {{-- Tenant Selector --}}
        <div style="background: #1c1410; border: 1px solid rgba(212,146,12,0.12); border-radius: 12px; padding: 1.5rem; margin-bottom: 1.5rem;">
            <label for="tenant-select" style="display: block; color: #d4920c; font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.1em; font-weight: 600; margin-bottom: 0.5rem;">
                Select Tenant
            </label>
            <select
                wire:model.live="selectedTenant"
                id="tenant-select"
                style="width: 100%; padding: 0.6rem 0.75rem; background: #2a1f18; border: 1px solid rgba(212,146,12,0.12); border-radius: 8px; color: #faf0d6; font-size: 0.9rem; outline: none;"
            >
                <option value="">— Choose a bakery —</option>
                @foreach($this->getTenants() as $id => $name)
                    <option value="{{ $id }}">{{ $name }}</option>
                @endforeach
            </select>
        </div>

        @if($selectedTenant)
            {{-- Export Grid --}}
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 1.5rem; margin-bottom: 1.5rem;">
                @foreach($this->getExportTypes() as $type => $info)
                    <div style="background: #1c1410; border: 1px solid rgba(212,146,12,0.12); border-radius: 12px; padding: 1.5rem; display: flex; flex-direction: column; justify-content: space-between;">
                        <div>
                            <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.75rem;">
                                <div style="width: 40px; height: 40px; background: rgba(212,146,12,0.1); border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="#d4920c" style="width: 22px; height: 22px;">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                                    </svg>
                                </div>
                                <div style="color: white; font-weight: 700; font-size: 1rem;">{{ $info['name'] }}</div>
                            </div>
                            <div style="color: #8b6844; font-size: 0.85rem; margin-bottom: 0.75rem;">{{ $info['description'] }}</div>
                            @if(isset($counts[$type]))
                                <div style="color: #d4920c; font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.1em; font-weight: 600; margin-bottom: 1rem;">
                                    {{ number_format($counts[$type]) }} rows
                                </div>
                            @endif
                        </div>
                        <a
                            href="{{ route('central.export', ['tenant' => $selectedTenant, 'type' => $type]) }}"
                            style="display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem; padding: 0.6rem 1rem; background: #d4920c; color: #1c1410; font-weight: 700; font-size: 0.85rem; border-radius: 8px; text-decoration: none;"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width: 16px; height: 16px;">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                            </svg>
                            Export CSV
                        </a>
                    </div>
                @endforeach

                {{-- All Data (ZIP) --}}
                <div style="background: #1c1410; border: 2px solid rgba(212,146,12,0.25); border-radius: 12px; padding: 1.5rem; display: flex; flex-direction: column; justify-content: space-between;">
                    <div>
                        <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.75rem;">
                            <div style="width: 40px; height: 40px; background: rgba(212,146,12,0.15); border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="#e8b04a" style="width: 22px; height: 22px;">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5m8.25 3v6.75m0 0-3-3m3 3 3-3M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" />
                                </svg>
                            </div>
                            <div style="color: white; font-weight: 700; font-size: 1rem;">All Data (ZIP)</div>
                        </div>
                        <div style="color: #8b6844; font-size: 0.85rem; margin-bottom: 1rem;">Download all data types as a single ZIP archive containing individual CSV files.</div>
                    </div>
                    <a
                        href="{{ route('central.export', ['tenant' => $selectedTenant, 'type' => 'all']) }}"
                        style="display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem; padding: 0.6rem 1rem; background: #e8b04a; color: #1c1410; font-weight: 700; font-size: 0.85rem; border-radius: 8px; text-decoration: none;"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width: 16px; height: 16px;">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                        </svg>
                        Download ZIP
                    </a>
                </div>
            </div>
        @else
            <div style="text-align: center; padding: 3rem 1.5rem; background: #1c1410; border: 1px solid rgba(212,146,12,0.12); border-radius: 12px;">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="rgba(212,146,12,0.3)" style="width: 48px; height: 48px; margin: 0 auto 1rem; display: block;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                </svg>
                <div style="color: #8b6844; font-size: 0.9rem;">Select a tenant above to view export options.</div>
            </div>
        @endif
    </div>
</x-filament-panels::page>
