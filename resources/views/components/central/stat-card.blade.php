@props(['label'])

<div style="background: #1c1410; border-radius: 0.75rem; padding: 1.25rem; border: 1px solid #2a1f18;">
    <div style="color: #d4920c; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em;">{{ $label }}</div>
    <div style="color: #faf0d6; font-size: 1.5rem; font-weight: 700; margin-top: 0.25rem;">{{ $slot }}</div>
</div>
