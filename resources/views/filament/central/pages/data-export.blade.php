<x-filament-panels::page>
    <div style="max-width: 1200px; margin: 0 auto;">
        {{-- Tenant Selector --}}
        <div style="background: #1c1410; border: 1px solid #d4920c33; border-radius: 12px; padding: 24px; margin-bottom: 24px;">
            <label for="tenant-select" style="display: block; font-size: 14px; font-weight: 600; color: #e8b04a; margin-bottom: 8px;">
                Select Tenant
            </label>
            <select
                wire:model.live="selectedTenant"
                id="tenant-select"
                style="width: 100%; padding: 10px 14px; background: #2a1f18; border: 1px solid #d4920c55; border-radius: 8px; color: #f5d88e; font-size: 15px; outline: none;"
            >
                <option value="">— Choose a bakery —</option>
                @foreach($this->getTenants() as $id => $name)
                    <option value="{{ $id }}">{{ $name }}</option>
                @endforeach
            </select>
        </div>

        @if($selectedTenant)
            {{-- Export Grid --}}
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 16px; margin-bottom: 24px;">
                @foreach($this->getExportTypes() as $type => $info)
                    <div style="background: #1c1410; border: 1px solid #d4920c33; border-radius: 12px; padding: 24px; display: flex; flex-direction: column; justify-content: space-between;">
                        <div>
                            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 12px;">
                                <div style="width: 40px; height: 40px; background: #d4920c22; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                                    <x-filament::icon :icon="$info['icon']" style="width: 22px; height: 22px; color: #d4920c;" />
                                </div>
                                <h3 style="font-size: 17px; font-weight: 700; color: #f5d88e; margin: 0;">{{ $info['name'] }}</h3>
                            </div>
                            <p style="font-size: 13px; color: #e8b04a99; margin: 0 0 12px 0;">{{ $info['description'] }}</p>
                            @if(isset($counts[$type]))
                                <p style="font-size: 13px; color: #d4920c; font-weight: 600; margin: 0 0 16px 0;">
                                    {{ number_format($counts[$type]) }} rows
                                </p>
                            @endif
                        </div>
                        <a
                            href="{{ route('central.export', ['tenant' => $selectedTenant, 'type' => $type]) }}"
                            style="display: inline-flex; align-items: center; justify-content: center; gap: 8px; padding: 10px 18px; background: linear-gradient(135deg, #d4920c, #e8b04a); color: #1c1410; font-weight: 700; font-size: 14px; border-radius: 8px; text-decoration: none; transition: opacity 0.2s;"
                            onmouseover="this.style.opacity='0.85'"
                            onmouseout="this.style.opacity='1'"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width: 16px; height: 16px;">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                            </svg>
                            Export CSV
                        </a>
                    </div>
                @endforeach

                {{-- All Data (ZIP) --}}
                <div style="background: linear-gradient(135deg, #2a1f18, #1c1410); border: 2px solid #d4920c66; border-radius: 12px; padding: 24px; display: flex; flex-direction: column; justify-content: space-between;">
                    <div>
                        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 12px;">
                            <div style="width: 40px; height: 40px; background: #d4920c33; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                                <x-filament::icon icon="heroicon-o-archive-box-arrow-down" style="width: 22px; height: 22px; color: #e8b04a;" />
                            </div>
                            <h3 style="font-size: 17px; font-weight: 700; color: #f5d88e; margin: 0;">All Data (ZIP)</h3>
                        </div>
                        <p style="font-size: 13px; color: #e8b04a99; margin: 0 0 16px 0;">Download all data types as a single ZIP archive containing individual CSV files.</p>
                    </div>
                    <a
                        href="{{ route('central.export', ['tenant' => $selectedTenant, 'type' => 'all']) }}"
                        style="display: inline-flex; align-items: center; justify-content: center; gap: 8px; padding: 10px 18px; background: linear-gradient(135deg, #e8b04a, #f5d88e); color: #1c1410; font-weight: 700; font-size: 14px; border-radius: 8px; text-decoration: none; transition: opacity 0.2s;"
                        onmouseover="this.style.opacity='0.85'"
                        onmouseout="this.style.opacity='1'"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width: 16px; height: 16px;">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                        </svg>
                        Download ZIP
                    </a>
                </div>
            </div>
        @else
            <div style="text-align: center; padding: 60px 20px; background: #1c1410; border: 1px solid #d4920c22; border-radius: 12px;">
                <x-filament::icon icon="heroicon-o-arrow-down-tray" style="width: 48px; height: 48px; color: #d4920c44; margin: 0 auto 16px;" />
                <p style="color: #e8b04a66; font-size: 15px; margin: 0;">Select a tenant above to view export options.</p>
            </div>
        @endif
    </div>
</x-filament-panels::page>
