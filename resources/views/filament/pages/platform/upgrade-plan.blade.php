<x-filament-panels::page>
    <div style="max-width: 1100px; margin: 0 auto;">
        {{-- Current Plan Banner --}}
        <div style="background: linear-gradient(135deg, #8B5E3C 0%, #A0724E 50%, #D4A574 100%); border-radius: 16px; padding: 24px 32px; margin-bottom: 32px; color: white; display: flex; align-items: center; gap: 16px; box-shadow: 0 4px 24px rgba(139, 94, 60, 0.3);">
            <div style="background: rgba(255,255,255,0.18); border-radius: 12px; padding: 12px; display: flex; align-items: center; justify-content: center;">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:28px;height:28px;"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8.25v-1.5m0 1.5c-1.355 0-2.697.056-4.024.166C6.845 8.51 6 9.473 6 10.608v2.513m6-4.871c1.355 0 2.697.056 4.024.166C17.155 8.51 18 9.473 18 10.608v2.513M15 8.25v-1.5m-6 1.5v-1.5m12 9.75-1.5.75a3.354 3.354 0 0 1-3 0 3.354 3.354 0 0 0-3 0 3.354 3.354 0 0 1-3 0 3.354 3.354 0 0 0-3 0 3.354 3.354 0 0 1-3 0L3 16.5m15-3.379a48.474 48.474 0 0 0-6-.371c-2.032 0-4.034.126-6 .371m12 0c.39.049.777.102 1.163.16 1.07.16 1.837 1.094 1.837 2.175v5.169c0 .621-.504 1.125-1.125 1.125H4.125A1.125 1.125 0 0 1 3 20.625v-5.17c0-1.08.768-2.014 1.837-2.174A47.78 47.78 0 0 1 6 13.12M12.265 3.11a.375.375 0 1 1-.53.53.375.375 0 0 1 .53-.53Zm0 0L12 3.375m.265-.265A.375.375 0 0 0 12 3v0m3.265.11a.375.375 0 1 1-.53.53.375.375 0 0 1 .53-.53Zm0 0L15 3.375m.265-.265A.375.375 0 0 0 15 3v0m-6 .265a.375.375 0 1 1-.53-.53.375.375 0 0 1 .53.53Zm0 0L9 3.375m-.265.265A.375.375 0 0 0 9 3v0" /></svg>
            </div>
            <div>
                <p style="margin: 0; font-size: 0.85rem; opacity: 0.85;">Your current plan</p>
                <h2 style="margin: 4px 0 0 0; font-size: 1.5rem; font-weight: 700;">
                    {{ $plans[$currentPlan]['name'] ?? 'Starter' }} — ${{ $plans[$currentPlan]['price'] ?? 9 }}/mo
                </h2>
            </div>
        </div>

        {{-- Plan Cards --}}
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px;">
            @foreach ($plans as $key => $plan)
                @php
                    $hierarchy = ['starter' => 1, 'growth' => 2, 'pro' => 3];
                    $isCurrent = $key === $currentPlan;
                    $isUpgrade = $hierarchy[$key] > $hierarchy[$currentPlan];
                    $isDowngrade = $hierarchy[$key] < $hierarchy[$currentPlan];
                    $cardBorder = $isCurrent ? '2px solid #d4920c' : '1px solid rgba(212,165,116,0.25)';
                    $cardShadow = $isCurrent ? '0 8px 32px rgba(212,146,12,0.15)' : '0 4px 16px rgba(61,35,20,0.06)';
                    $headerBg = match($key) {
                        'starter' => 'linear-gradient(135deg, #6b4c3b, #8B5E3C)',
                        'growth' => 'linear-gradient(135deg, #8B5E3C, #d4920c)',
                        'pro' => 'linear-gradient(135deg, #3d2314, #6b4c3b)',
                    };
                @endphp
                <div style="border-radius: 16px; border: {{ $cardBorder }}; box-shadow: {{ $cardShadow }}; background: #fff; display: flex; flex-direction: column; overflow: hidden; {{ $isCurrent ? 'transform: scale(1.02);' : '' }}">
                    {{-- Card Header --}}
                    <div style="background: {{ $headerBg }}; padding: 24px; color: white; position: relative;">
                        @if ($isCurrent)
                            <span style="position: absolute; top: 12px; right: 12px; background: rgba(255,255,255,0.2); color: white; font-size: 0.7rem; font-weight: 600; padding: 4px 10px; border-radius: 20px; text-transform: uppercase; letter-spacing: 0.5px;">Current</span>
                        @elseif ($key === 'pro')
                            <span style="position: absolute; top: 12px; right: 12px; background: rgba(255,255,255,0.2); color: white; font-size: 0.7rem; font-weight: 600; padding: 4px 10px; border-radius: 20px; text-transform: uppercase; letter-spacing: 0.5px;">Best Value</span>
                        @endif
                        <h3 style="margin: 0; font-size: 1.1rem; font-weight: 600; opacity: 0.9;">{{ $plan['name'] }}</h3>
                        <div style="margin-top: 8px;">
                            <span style="font-size: 2.5rem; font-weight: 800; line-height: 1;">${{ $plan['price'] }}</span>
                            <span style="font-size: 0.9rem; opacity: 0.7;">/month</span>
                        </div>
                    </div>

                    {{-- Features --}}
                    <div style="padding: 24px; flex: 1; display: flex; flex-direction: column;">
                        <ul style="list-style: none; margin: 0; padding: 0; flex: 1;">
                            @foreach ($plan['features'] as $feature)
                                <li style="display: flex; align-items: flex-start; gap: 10px; padding: 8px 0; {{ !$loop->last ? 'border-bottom: 1px solid rgba(212,165,116,0.1);' : '' }}">
                                    @if (str_contains($feature, 'Everything in'))
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" style="width:16px;height:16px;color:#d4920c;flex-shrink:0;margin-top:2px;"><path fill-rule="evenodd" d="M11.47 2.47a.75.75 0 0 1 1.06 0l7.5 7.5a.75.75 0 1 1-1.06 1.06l-6.22-6.22V21a.75.75 0 0 1-1.5 0V4.81l-6.22 6.22a.75.75 0 1 1-1.06-1.06l7.5-7.5Z" clip-rule="evenodd" /></svg>
                                        <span style="font-size: 0.875rem; color: #8B5E3C; font-weight: 600;">{{ $feature }}</span>
                                    @else
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" style="width:16px;height:16px;color:#22c55e;flex-shrink:0;margin-top:2px;"><path fill-rule="evenodd" d="M19.916 4.626a.75.75 0 0 1 .208 1.04l-9 13.5a.75.75 0 0 1-1.154.114l-6-6a.75.75 0 0 1 1.06-1.06l5.353 5.353 8.493-12.74a.75.75 0 0 1 1.04-.207Z" clip-rule="evenodd" /></svg>
                                        <span style="font-size: 0.875rem; color: #4a3728;">{{ $feature }}</span>
                                    @endif
                                </li>
                            @endforeach
                        </ul>

                        {{-- Action Button --}}
                        <div style="margin-top: 20px;">
                            @if ($isUpgrade)
                                <button
                                    wire:click="redirectToBilling"
                                    style="width: 100%; padding: 12px 16px; border-radius: 10px; font-weight: 700; font-size: 0.9rem; text-align: center; cursor: pointer; border: none; color: white; background: {{ $key === 'pro' ? 'linear-gradient(135deg, #3d2314, #6b4c3b)' : 'linear-gradient(135deg, #d4920c, #e8b04a)' }}; box-shadow: 0 4px 12px {{ $key === 'pro' ? 'rgba(61,35,20,0.3)' : 'rgba(212,146,12,0.3)' }}; transition: transform 0.15s, box-shadow 0.15s;"
                                    onmouseover="this.style.transform='translateY(-1px)';this.style.boxShadow='0 6px 16px rgba(212,146,12,0.4)'"
                                    onmouseout="this.style.transform='translateY(0)';this.style.boxShadow='0 4px 12px rgba(212,146,12,0.3)'"
                                >
                                    Upgrade to {{ $plan['name'] }}
                                </button>
                            @elseif ($isCurrent)
                                <div style="width: 100%; padding: 12px 16px; border-radius: 10px; font-weight: 600; font-size: 0.9rem; text-align: center; background: rgba(212,165,116,0.15); color: #8B5E3C; border: 1px solid rgba(212,165,116,0.3);">
                                    Your Current Plan
                                </div>
                            @else
                                <div style="width: 100%; padding: 12px 16px; border-radius: 10px; font-weight: 600; font-size: 0.9rem; text-align: center; background: #f5f0eb; color: #a08060;">
                                    Included in your plan
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Help Text --}}
        <div style="margin-top: 32px; text-align: center; font-size: 0.85rem; color: #8b6844;">
            <p style="margin: 0;">Need help choosing? Reach out to us anytime.</p>
            <p style="margin: 6px 0 0 0; opacity: 0.7;">All plans include a 30-day free trial. Cancel anytime.</p>
        </div>
    </div>
</x-filament-panels::page>
