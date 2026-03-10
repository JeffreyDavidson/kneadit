<x-filament-panels::page>
    @php $activities = $this->getActivities(); @endphp

    @if($activities->isEmpty())
        <div style="text-align: center; padding: 4rem 2rem;">
            <div style="margin-bottom: 1rem;">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: 48px; height: 48px; color: #8b6844; margin: 0 auto; display: block;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15a2.25 2.25 0 0 1 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z" />
                </svg>
            </div>
            <div style="color: #ffffff; font-size: 1.125rem; font-weight: 600; margin-bottom: 0.5rem;">No activity yet</div>
            <div style="color: #8b6844; font-size: 0.875rem;">Platform events will appear here as they happen — tenant signups, plan changes, and more.</div>
        </div>
    @else
        <div style="position: relative; padding-left: 2.5rem;">
            <div style="position: absolute; left: 0.9rem; top: 0; bottom: 0; width: 2px; background: rgba(212,146,12,0.2);"></div>
            @foreach($activities as $activity)
                @php
                    $eventColor = \App\Filament\Central\Pages\ActivityLog::getEventColor($activity->event);
                    $eventIcon = \App\Filament\Central\Pages\ActivityLog::getEventIcon($activity->event);
                @endphp
                <div style="position: relative; margin-bottom: 1.5rem;">
                    <div style="position: absolute; left: -2.5rem; top: 0.125rem; width: 1.75rem; height: 1.75rem; border-radius: 50%; display: flex; align-items: center; justify-content: center; background: #1c1410; border: 2px solid {{ $eventColor }}; z-index: 1;">
                        @if($eventIcon === 'heroicon-o-user-plus')
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: 14px; height: 14px; color: {{ $eventColor }};">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM3 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 9.374 21c-2.331 0-4.512-.645-6.374-1.766Z" />
                            </svg>
                        @elseif($eventIcon === 'heroicon-o-arrow-path')
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: 14px; height: 14px; color: {{ $eventColor }};">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182M2.985 19.644l3.181-3.182" />
                            </svg>
                        @elseif($eventIcon === 'heroicon-o-trash')
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: 14px; height: 14px; color: {{ $eventColor }};">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                            </svg>
                        @else
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: 14px; height: 14px; color: {{ $eventColor }};">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                        @endif
                    </div>
                    <div style="background: #1c1410; border: 1px solid rgba(212,146,12,0.12); border-radius: 12px; padding: 1rem 1.25rem;">
                        <div style="display: inline-block; font-size: 0.65rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.1em; padding: 0.125rem 0.5rem; border-radius: 9999px; background: rgba(212,146,12,0.15); color: #f5d88e; margin-bottom: 0.375rem;">{{ str_replace('_', ' ', $activity->event) }}</div>
                        <div style="color: #faf0d6; font-size: 0.875rem;">{{ $activity->description }}</div>
                        <div style="color: #8b6844; font-size: 0.75rem; margin-top: 0.25rem;">
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
