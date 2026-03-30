<x-filament-widgets::widget>
    <x-filament::section heading="Upcoming Birthdays" icon="heroicon-o-cake" collapsible>
        @php
            $birthdays = $this->getUpcomingBirthdays();
        @endphp

        @if ($birthdays->isEmpty())
            <p style="font-size: 0.875rem; color: var(--brand-500);">No upcoming birthdays in the next 30 days.</p>
        @else
            <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                @foreach ($birthdays as $entry)
                    <div style="display: flex; align-items: center; justify-content: space-between; padding: 0.5rem; border-radius: 0.75rem; {{ $entry->is_today ? 'background: var(--accent-gold-light); border: 1px solid var(--brand-200);' : 'background: var(--brand-50);' }}">
                        <div style="display: flex; align-items: center; gap: 0.75rem;">
                            @if ($entry->is_today)
                                <span style="font-size: 1.5rem;"></span>
                            @else
                                <span style="font-size: 1.125rem;"></span>
                            @endif
                            <div>
                                <p style="font-weight: 500; font-size: 0.875rem; color: var(--brand-900); margin: 0;">
                                    {{ $entry->customer->name }}
                                </p>
                                <p style="font-size: 0.75rem; color: var(--brand-500); margin: 0;">
                                    {{ $entry->birthday_date }}
                                </p>
                            </div>
                        </div>
                        <div>
                            @if ($entry->is_today)
                                <span style="display: inline-flex; align-items: center; border-radius: 9999px; background: var(--accent-gold); padding: 0.125rem 0.625rem; font-size: 0.75rem; font-weight: 500; color: var(--brand-900);">
                                    Today!
                                </span>
                            @else
                                <span style="font-size: 0.75rem; color: var(--brand-500);">
                                    in {{ $entry->days_until }} {{ Str::plural('day', $entry->days_until) }}
                                </span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
