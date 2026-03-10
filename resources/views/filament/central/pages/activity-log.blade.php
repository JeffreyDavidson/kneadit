<x-filament-panels::page>
    <style>
        .activity-timeline { position: relative; padding-left: 2.5rem; }
        .activity-timeline::before { content: ''; position: absolute; left: 0.9rem; top: 0; bottom: 0; width: 2px; background: rgba(212,146,12,0.2); }
        .activity-item { position: relative; margin-bottom: 1.5rem; }
        .activity-icon { position: absolute; left: -2.5rem; top: 0.125rem; width: 1.75rem; height: 1.75rem; border-radius: 50%; display: flex; align-items: center; justify-content: center; background: #1c1410; border: 2px solid rgba(212,146,12,0.3); z-index: 1; }
        .activity-icon svg { width: 0.875rem; height: 0.875rem; }
        .activity-card { background: #1c1410; border: 1px solid rgba(212,146,12,0.12); border-radius: 0.75rem; padding: 1rem 1.25rem; }
        .activity-desc { color: #faf0d6; font-size: 0.875rem; }
        .activity-meta { color: #8b6844; font-size: 0.75rem; margin-top: 0.25rem; }
        .activity-event-badge { display: inline-block; font-size: 0.625rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; padding: 0.125rem 0.5rem; border-radius: 9999px; background: rgba(212,146,12,0.15); color: #f5d88e; margin-bottom: 0.375rem; }
        .empty-state { text-align: center; padding: 4rem 2rem; }
        .empty-state-icon { color: #5c4333; margin-bottom: 1rem; }
        .empty-state-title { color: #ffffff; font-size: 1.125rem; font-weight: 600; margin-bottom: 0.5rem; }
        .empty-state-desc { color: #8b6844; font-size: 0.875rem; }
    </style>

    @php $activities = $this->getActivities(); @endphp

    @if($activities->isEmpty())
        <div class="empty-state">
            <div class="empty-state-icon">
                <x-filament::icon icon="heroicon-o-clipboard-document-list" class="h-12 w-12 mx-auto" style="color: #5c4333;" />
            </div>
            <div class="empty-state-title">No activity yet</div>
            <div class="empty-state-desc">Platform events will appear here as they happen — tenant signups, plan changes, and more.</div>
        </div>
    @else
        <div class="activity-timeline">
            @foreach($activities as $activity)
                <div class="activity-item">
                    <div class="activity-icon" style="border-color: {{ \App\Filament\Central\Pages\ActivityLog::getEventColor($activity->event) }};">
                        <x-filament::icon
                            :icon="\App\Filament\Central\Pages\ActivityLog::getEventIcon($activity->event)"
                            style="color: {{ \App\Filament\Central\Pages\ActivityLog::getEventColor($activity->event) }};"
                        />
                    </div>
                    <div class="activity-card">
                        <div class="activity-event-badge">{{ str_replace('_', ' ', $activity->event) }}</div>
                        <div class="activity-desc">{{ $activity->description }}</div>
                        <div class="activity-meta">
                            @if($activity->tenant_id)
                                Tenant: {{ $activity->tenant_id }} ·
                            @endif
                            {{ $activity->created_at->diffForHumans() }}
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</x-filament-panels::page>
