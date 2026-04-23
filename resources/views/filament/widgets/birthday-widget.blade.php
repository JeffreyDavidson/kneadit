<x-filament-widgets::widget>
    <x-filament::section heading="Upcoming Birthdays" icon="heroicon-o-cake" collapsible>
        @php $birthdays = $this->getUpcomingBirthdays(); @endphp

        @if ($birthdays->isEmpty())
            <p class="text-sm text-brand-500">No upcoming birthdays in the next 30 days.</p>
        @else
            <div class="flex flex-col gap-3">
                @foreach ($birthdays as $entry)
                    <div @class([
                        'flex items-center justify-between p-2 rounded-xl',
                        'bg-brand-100 border border-brand-200' => $entry->is_today,
                        'bg-brand-50' => ! $entry->is_today,
                    ])>
                        <div class="flex items-center gap-3">
                            <span class="{{ $entry->is_today ? 'text-2xl' : 'text-lg' }}">🎂</span>
                            <div>
                                <p class="font-medium text-sm text-brand-900 m-0">
                                    {{ $entry->customer_name }}
                                </p>
                                <p class="text-xs text-brand-500 m-0">
                                    {{ $entry->birthday_date }}
                                </p>
                            </div>
                        </div>
                        <div>
                            @if ($entry->is_today)
                                <span class="inline-flex items-center rounded-full bg-brand-300 px-2.5 py-0.5 text-xs font-medium text-brand-900">
                                    Today!
                                </span>
                            @else
                                <span class="text-xs text-brand-500">
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
