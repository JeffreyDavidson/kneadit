@props(['title'])
<div style="background: linear-gradient(135deg, #3d2314, #6b4c3b); border-radius: 16px; padding: 24px 28px; margin-bottom: 24px;">
    <h2 style="color: white; font-size: 1.3rem; font-weight: 700; margin: 0;">{{ $title }}</h2>
    @if ($slot->isNotEmpty())
        <div style="color: rgba(255,255,255,0.8); margin-top: 8px; font-size: 0.9rem;">{{ $slot }}</div>
    @endif
</div>
