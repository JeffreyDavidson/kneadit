@php
    $subject = $get('subject') ?: 'Subject line appears here';
    $body = $get('body') ?: 'Your message body will preview here as you type.';
    $segmentRaw = $get('target_segment');
    $segmentValue = $segmentRaw instanceof \BackedEnum ? $segmentRaw->value : ($segmentRaw ?: 'all');
    $segmentLabel = ucfirst((string) $segmentValue);
@endphp

<div>
    <div class="mb-2 flex items-center justify-between">
        <span class="text-cinnamon text-[0.7rem] font-semibold tracking-[0.08em] uppercase">Live preview</span>
        <span class="text-cinnamon text-[0.7rem]">Sends to <span class="text-honey font-semibold">{{ $segmentLabel }}</span> segment</span>
    </div>

    {{-- Email client header --}}
    <div class="border-honey/15 flex items-center gap-2.5 rounded-t-lg border border-b-0 bg-white px-4 py-2.5">
        <span class="bg-warm-black text-honey inline-flex h-7 w-7 items-center justify-center rounded-full text-[0.7rem] font-bold">K</span>
        <div class="min-w-0 flex-1">
            <div class="truncate text-[0.8rem] font-semibold text-gray-900">KneadIt Platform</div>
            <div class="text-[0.7rem] text-gray-500">noreply@getkneadit.app</div>
        </div>
    </div>

    {{-- Subject strip --}}
    <div class="border-honey/15 border-x border-b border-gray-100 bg-white px-4 py-2">
        <div class="text-[0.9rem] font-semibold text-gray-900">{{ $subject }}</div>
    </div>

    {{-- Branded body --}}
    <div class="overflow-hidden rounded-b-lg" style="background-color: #fef9ef; padding: 16px">
        <div
            style="
                max-width: 100%;
                margin: 0 auto;
                background-color: #ffffff;
                border-radius: 10px;
                overflow: hidden;
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            "
        >
            <div
                style="
                    background: linear-gradient(135deg, #1c1410 0%, #2a1f18 100%);
                    text-align: center;
                    padding: 22px 20px;
                "
            >
                <div style="margin: 0; font-size: 22px; font-weight: 700; color: #d4920c; letter-spacing: -0.3px">
                    KneadIt
                </div>
                <div style="margin: 4px 0 0; color: #d4a574; font-size: 11px">Your bakery management platform</div>
            </div>
            <div style="padding: 22px 26px; color: #1c1410">
                <div style="color: #1c1410; font-size: 13px; line-height: 1.55; white-space: pre-wrap">{{ $body }}</div>
            </div>
            <div style="background-color: #fef9ef; padding: 14px 26px; border-top: 1px solid #e8d0b0; text-align: center">
                <div style="color: #6b4c3b; font-size: 10.5px">
                    KneadIt · Platform announcement to the {{ $segmentLabel }} segment
                </div>
            </div>
        </div>
    </div>
</div>
