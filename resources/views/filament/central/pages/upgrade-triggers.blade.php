<x-filament-panels::page>
    @php $tenants = $this->getTenantUsageData(); @endphp

    @if($tenants->isEmpty())
        {{-- Empty State --}}
        <div style="background: #1c1410; border: 1px solid rgba(212,146,12,0.12); border-radius: 12px; padding: 3rem; text-align: center;">
            <div style="margin-bottom: 1rem;">
                <svg style="width: 48px; height: 48px; display: inline-block;" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            </div>
            <div style="color: #10b981; font-weight: 700; font-size: 1rem; margin-bottom: 0.5rem;">All Tenants Within Limits</div>
            <p style="color: #8b6844; max-width: 480px; margin: 0 auto;">
                No bakeries are currently approaching their plan limits. When tenants reach 80% or more of their product or order limits, they'll appear here as upgrade candidates.
            </p>
        </div>
    @else
        <div style="margin-bottom: 1rem;">
            <p style="color: #8b6844; font-size: 0.875rem;">
                <span style="color: #d4920c; font-weight: 700;">{{ $tenants->count() }}</span> tenant{{ $tenants->count() !== 1 ? 's' : '' }} approaching or at plan limits
            </p>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(380px, 1fr)); gap: 1rem;">
            @foreach($tenants as $t)
                <div style="background: #1c1410; border: 1px solid {{ $t['at_limit'] ? '#ef4444' : 'rgba(245,158,11,0.3)' }}; border-radius: 12px; padding: 1.5rem; transition: transform 0.2s, box-shadow 0.2s;">
                    {{-- Header --}}
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem;">
                        <div>
                            <div style="color: white; font-size: 1rem; font-weight: 700; margin-bottom: 0.15rem;">{{ $t['name'] }}</div>
                            <span style="color: #8b6844; font-size: 0.75rem;">{{ $t['plan'] }} Plan</span>
                        </div>
                        @if($t['at_limit'])
                            <span style="display: inline-block; background: rgba(239,68,68,0.15); color: #ef4444; border-radius: 9999px; padding: 0.2rem 0.6rem; font-size: 0.7rem; font-weight: 600;">At Limit</span>
                        @else
                            <span style="display: inline-block; background: rgba(245,158,11,0.15); color: #f59e0b; border-radius: 9999px; padding: 0.2rem 0.6rem; font-size: 0.7rem; font-weight: 600;">Approaching</span>
                        @endif
                    </div>

                    {{-- Products Bar --}}
                    <div style="margin-bottom: 0.75rem;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 0.25rem;">
                            <span style="color: #d4920c; font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.1em; font-weight: 600;">Products</span>
                            <span style="color: #faf0d6; font-size: 0.75rem; font-weight: 600;">{{ $t['product_count'] }} / {{ $t['product_limit'] }}</span>
                        </div>
                        <div style="background: #2a1f18; border-radius: 4px; height: 8px; overflow: hidden;">
                            @php
                                $pColor = $t['product_percent'] >= 100 ? '#ef4444' : ($t['product_percent'] >= 80 ? '#f59e0b' : '#10b981');
                            @endphp
                            <div style="width: {{ min($t['product_percent'], 100) }}%; background: {{ $pColor }}; height: 100%; border-radius: 4px; transition: width 0.3s;"></div>
                        </div>
                    </div>

                    {{-- Orders Bar --}}
                    <div style="margin-bottom: 1rem;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 0.25rem;">
                            <span style="color: #d4920c; font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.1em; font-weight: 600;">Orders This Month</span>
                            <span style="color: #faf0d6; font-size: 0.75rem; font-weight: 600;">{{ $t['order_count'] }} / {{ $t['order_limit'] }}</span>
                        </div>
                        <div style="background: #2a1f18; border-radius: 4px; height: 8px; overflow: hidden;">
                            @php
                                $oColor = $t['order_percent'] >= 100 ? '#ef4444' : ($t['order_percent'] >= 80 ? '#f59e0b' : '#10b981');
                            @endphp
                            <div style="width: {{ min($t['order_percent'], 100) }}%; background: {{ $oColor }}; height: 100%; border-radius: 4px; transition: width 0.3s;"></div>
                        </div>
                    </div>

                    {{-- Divider --}}
                    <div style="border-top: 1px solid rgba(212,146,12,0.08); padding-top: 1rem;">
                        {{-- Upgrade Button --}}
                        @php $nextPlan = $this->getNextPlan($t['plan_key']); @endphp
                        @if($nextPlan)
                            <button
                                wire:click="suggestUpgrade('{{ $t['tenant']->id }}')"
                                style="width: 100%; background: #d4920c; color: #1c1410; border: none; border-radius: 8px; padding: 0.6rem; font-size: 0.8rem; font-weight: 700; cursor: pointer;"
                            >
                                Suggest Upgrade to {{ $nextPlan }}
                            </button>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</x-filament-panels::page>
