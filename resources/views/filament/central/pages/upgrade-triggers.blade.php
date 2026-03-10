<x-filament-panels::page>
    @php $tenants = $this->getTenantUsageData(); @endphp

    @if($tenants->isEmpty())
        {{-- Empty State --}}
        <div style="background: #1c1410; border: 1px solid #3d2c1e; border-radius: 12px; padding: 48px; text-align: center;">
            <div style="font-size: 48px; margin-bottom: 16px;">🎉</div>
            <h3 style="color: #10b981; font-size: 20px; font-weight: 600; margin-bottom: 8px;">All Tenants Within Limits</h3>
            <p style="color: #a89580; max-width: 480px; margin: 0 auto;">
                No bakeries are currently approaching their plan limits. When tenants reach 80% or more of their product or order limits, they'll appear here as upgrade candidates.
            </p>
        </div>
    @else
        <div style="margin-bottom: 16px;">
            <p style="color: #a89580; font-size: 14px;">
                <span style="color: #e8b04a; font-weight: 600;">{{ $tenants->count() }}</span> tenant{{ $tenants->count() !== 1 ? 's' : '' }} approaching or at plan limits
            </p>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(380px, 1fr)); gap: 16px;">
            @foreach($tenants as $t)
                <div style="background: #1c1410; border: 1px solid {{ $t['at_limit'] ? '#ef4444' : '#f59e0b' }}; border-radius: 12px; padding: 20px;">
                    {{-- Header --}}
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px;">
                        <div>
                            <h4 style="color: #f5d88e; font-size: 16px; font-weight: 600; margin: 0;">{{ $t['name'] }}</h4>
                            <span style="color: #a89580; font-size: 12px;">{{ $t['plan'] }} Plan</span>
                        </div>
                        @if($t['at_limit'])
                            <span style="background: #ef4444; color: #fff; font-size: 11px; font-weight: 700; padding: 4px 10px; border-radius: 9999px;">At Limit</span>
                        @else
                            <span style="background: #f59e0b; color: #0c0a09; font-size: 11px; font-weight: 700; padding: 4px 10px; border-radius: 9999px;">Approaching Limit</span>
                        @endif
                    </div>

                    {{-- Products Bar --}}
                    <div style="margin-bottom: 12px;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                            <span style="color: #a89580; font-size: 12px;">Products</span>
                            <span style="color: #f5d88e; font-size: 12px; font-weight: 600;">{{ $t['product_count'] }} / {{ $t['product_limit'] }}</span>
                        </div>
                        <div style="background: #2a1f18; border-radius: 6px; height: 10px; overflow: hidden;">
                            @php
                                $pColor = $t['product_percent'] >= 100 ? '#ef4444' : ($t['product_percent'] >= 80 ? '#f59e0b' : '#10b981');
                            @endphp
                            <div style="width: {{ $t['product_percent'] }}%; background: {{ $pColor }}; height: 100%; border-radius: 6px; transition: width 0.3s;"></div>
                        </div>
                    </div>

                    {{-- Orders Bar --}}
                    <div style="margin-bottom: 16px;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                            <span style="color: #a89580; font-size: 12px;">Orders This Month</span>
                            <span style="color: #f5d88e; font-size: 12px; font-weight: 600;">{{ $t['order_count'] }} / {{ $t['order_limit'] }}</span>
                        </div>
                        <div style="background: #2a1f18; border-radius: 6px; height: 10px; overflow: hidden;">
                            @php
                                $oColor = $t['order_percent'] >= 100 ? '#ef4444' : ($t['order_percent'] >= 80 ? '#f59e0b' : '#10b981');
                            @endphp
                            <div style="width: {{ $t['order_percent'] }}%; background: {{ $oColor }}; height: 100%; border-radius: 6px; transition: width 0.3s;"></div>
                        </div>
                    </div>

                    {{-- Upgrade Button --}}
                    @php $nextPlan = $this->getNextPlan($t['plan_key']); @endphp
                    @if($nextPlan)
                        <button
                            wire:click="suggestUpgrade('{{ $t['tenant']->id }}')"
                            style="width: 100%; background: linear-gradient(135deg, #d4920c, #e8b04a); color: #0c0a09; border: none; border-radius: 8px; padding: 10px; font-size: 13px; font-weight: 700; cursor: pointer;"
                        >
                            Suggest Upgrade to {{ $nextPlan }}
                        </button>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
</x-filament-panels::page>
