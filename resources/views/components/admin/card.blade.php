@props(['title' => null, 'subtitle' => null])
<div style="background: white; border: 1px solid rgba(212,165,116,0.15); border-radius: 12px; overflow: hidden;" {{ $attributes }}>
    @if ($title)
        <div style="background: linear-gradient(135deg, #3d2314, #6b4c3b); padding: 14px 20px; display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h3 style="color: white; font-size: 0.95rem; font-weight: 600; margin: 0;">{{ $title }}</h3>
                @if ($subtitle)
                    <p style="color: rgba(255,255,255,0.7); font-size: 0.75rem; margin: 4px 0 0;">{{ $subtitle }}</p>
                @endif
            </div>
        </div>
    @endif
    <div style="padding: 16px 20px;">
        {{ $slot }}
    </div>
</div>
