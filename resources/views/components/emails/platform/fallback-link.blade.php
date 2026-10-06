{{-- Shows the raw link for mail clients where the button doesn't work. --}}
@props(['url'])
<div style="background: #fef9ef; border-radius: 8px; padding: 16px 20px; margin: 24px 0 0; border: 1px solid #e8d0b0">
    <p style="margin: 0 0 6px; color: #6b4c3b; font-size: 13px">
        If the button doesn't work, copy this link into your browser:
    </p>
    <p style="margin: 0; font-size: 12px; word-break: break-all">
        <a href="{{ $url }}" style="color: #8a5a0a">{{ $url }}</a>
    </p>
</div>
