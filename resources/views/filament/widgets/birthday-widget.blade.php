<x-filament-widgets::widget>
    <x-filament::section heading="🎂 Upcoming Birthdays" icon="heroicon-o-cake" collapsible>
        @php
            $birthdays = $this->getUpcomingBirthdays();
        @endphp

        @if($birthdays->isEmpty())
            <p class="text-sm text-gray-500 dark:text-gray-400">No upcoming birthdays in the next 30 days.</p>
        @else
            <div class="space-y-3">
                @foreach($birthdays as $entry)
                    <div class="flex items-center justify-between p-2 rounded-lg {{ $entry->is_today ? 'bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800' : 'bg-gray-50 dark:bg-gray-800' }}">
                        <div class="flex items-center gap-3">
                            @if($entry->is_today)
                                <span class="text-2xl">🎂</span>
                            @else
                                <span class="text-lg">🎈</span>
                            @endif
                            <div>
                                <p class="font-medium text-sm text-gray-900 dark:text-white">
                                    {{ $entry->customer->name }}
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ $entry->birthday_date }}
                                </p>
                            </div>
                        </div>
                        <div>
                            @if($entry->is_today)
                                <span class="inline-flex items-center rounded-full bg-yellow-100 px-2.5 py-0.5 text-xs font-medium text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                                    Today! 🎉
                                </span>
                            @else
                                <span class="text-xs text-gray-500 dark:text-gray-400">
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
