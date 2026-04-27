@php
    $subject = $get('subject') ?: 'Email subject goes here';
    $body = $get('body') ?: 'Your message body will preview here as you type.';
    $days = $get('days_after_signup');
    $isActive = (bool) $get('is_active');
@endphp

<div>
    <div class="flex items-center justify-between mb-2">
        <span class="text-cinnamon text-[0.7rem] uppercase tracking-[0.08em] font-semibold">Email preview</span>
        @if ($days)
            <span class="text-cinnamon text-[0.7rem]">Sends {{ $days }} {{ \Illuminate\Support\Str::plural('day', (int) $days) }} after signup</span>
        @endif
    </div>

    {{-- Email client header strip (simulated inbox row) --}}
    <div class="bg-white rounded-t-lg border border-b-0 border-honey/15 px-4 py-2.5 flex items-center gap-2.5">
        <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-warm-black text-honey text-[0.7rem] font-bold">K</span>
        <div class="min-w-0 flex-1">
            <div class="text-[0.8rem] text-gray-900 font-semibold truncate">KneadIt Platform</div>
            <div class="text-[0.7rem] text-gray-500">noreply@getkneadit.app → new bakers</div>
        </div>
        @if (! $isActive)
            <span class="inline-flex items-center gap-1 text-[0.65rem] uppercase tracking-[0.08em] font-bold text-amber-700 bg-amber-100 border border-amber-200 rounded px-2 py-0.5">
                Paused
            </span>
        @endif
    </div>

    {{-- Subject shown as email-list subject line --}}
    <div class="bg-white border-x border-honey/15 px-4 py-2 border-b border-gray-100">
        <div class="text-[0.9rem] text-gray-900 font-semibold">{{ $subject }}</div>
    </div>

    {{-- Branded email body (mirrors emails.platform.scheduled-checkin) --}}
    <div class="rounded-b-lg overflow-hidden" style="background-color: #fef9ef; padding: 16px;">
        <div style="max-width: 100%; margin: 0 auto; background-color: #ffffff; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">

            {{-- Honey-on-warm-black header --}}
            <div style="background: linear-gradient(135deg, #1c1410 0%, #2a1f18 100%); text-align: center; padding: 22px 20px;">
                <div style="margin: 0; font-size: 22px; font-weight: 700; color: #d4920c; letter-spacing: -0.3px;">KneadIt</div>
                <div style="margin: 4px 0 0; color: #d4a574; font-size: 11px;">Your bakery management platform</div>
            </div>

            {{-- Content --}}
            <div style="padding: 22px 26px; color: #1c1410;">
                <div style="margin: 0 0 14px; color: #4a3728; font-size: 13px;">Hi [Baker Name],</div>
                <div style="color: #1c1410; font-size: 13px; line-height: 1.55; white-space: pre-wrap;">{{ $body }}</div>
                <div style="text-align: center; margin: 22px 0 4px;">
                    <span style="display: inline-block; background: #d4920c; color: #ffffff; padding: 9px 20px; border-radius: 7px; font-weight: 600; font-size: 12.5px;">
                        Open Your Dashboard →
                    </span>
                </div>
            </div>

            {{-- Footer --}}
            <div style="background-color: #fef9ef; padding: 14px 26px; border-top: 1px solid #e8d0b0; text-align: center;">
                <div style="color: #6b4c3b; font-size: 10.5px; line-height: 1.5;">
                    You're receiving this as part of onboarding for your KneadIt bakery.
                </div>
            </div>
        </div>
    </div>
</div>
