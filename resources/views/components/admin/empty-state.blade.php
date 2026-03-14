@props(['icon' => '', 'title' => 'No data found', 'subtitle' => ''])

<div style="text-align: center; padding: 40px 20px; color: var(--brand-500, #a08060);">
    @if($icon)
        <div style="font-size: 2.5rem; margin-bottom: 12px;">{{ $icon }}</div>
    @endif
    <h3 style="font-size: 1.1rem; font-weight: 600; color: var(--brand-700, #6b4c3b); margin: 0 0 6px 0;">{{ $title }}</h3>
    @if($subtitle)
        <p style="margin: 0; font-size: 0.9rem;">{{ $subtitle }}</p>
    @endif
</div>
