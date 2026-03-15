<div>
    @foreach($this->getAnnouncements() as $announcement)
        <div
            x-data="{ dismissed: localStorage.getItem('announcement-dismissed-{{ $announcement['id'] }}') === 'true' }"
            x-show="!dismissed"
            x-cloak
            style="
                padding: 12px 16px;
                margin-bottom: 12px;
                border-radius: 8px;
                display: flex;
                align-items: flex-start;
                justify-content: space-between;
                gap: 12px;
                border: 1px solid {{ match($announcement['type']) {
                    'info' => '#3b82f6',
                    'warning' => '#d4920c',
                    'success' => '#10b981',
                    'maintenance' => '#6b7280',
                    default => '#3b82f6',
                } }}40;
                background: {{ match($announcement['type']) {
                    'info' => '#3b82f6',
                    'warning' => '#d4920c',
                    'success' => '#10b981',
                    'maintenance' => '#6b7280',
                    default => '#3b82f6',
                } }}15;
                color: #1c1410;
            "
        >
            <div style="flex: 1;">
                <div style="font-weight: 600; font-size: 0.9rem; margin-bottom: 4px; color: {{ match($announcement['type']) {
                    'info' => '#3b82f6',
                    'warning' => '#d4920c',
                    'success' => '#10b981',
                    'maintenance' => '#6b7280',
                    default => '#3b82f6',
                } }};">
                    @if($announcement['type'] === 'warning') ⚠️
                    @elseif($announcement['type'] === 'info') ℹ️
                    @elseif($announcement['type'] === 'success') ✅
                    @elseif($announcement['type'] === 'maintenance')
                    @endif
                    {{ $announcement['title'] }}
                </div>
                <div style="font-size: 0.85rem; line-height: 1.5;">
                    {!! $announcement['body'] !!}
                </div>
            </div>
            @if($announcement['is_dismissable'])
                <button
                    x-on:click="dismissed = true; localStorage.setItem('announcement-dismissed-{{ $announcement['id'] }}', 'true')"
                    style="
                        background: none;
                        border: none;
                        cursor: pointer;
                        font-size: 1.2rem;
                        color: #6b7280;
                        padding: 0 4px;
                        line-height: 1;
                    "
                    title="Dismiss"
                >&times;</button>
            @endif
        </div>
    @endforeach
</div>
