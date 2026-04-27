@php $birthdays = $this->getUpcomingBirthdays(); @endphp

<x-admin.dashboard.preview-card heading="Upcoming Birthdays" icon="🎂">
    @if ($birthdays->isEmpty())
        <div style="text-align: center; padding: 12px 0; color: var(--pw-card-text-muted); font-size: 0.75rem;">
            No upcoming birthdays in the next 30 days
        </div>
    @else
        @foreach ($birthdays as $entry)
            <x-admin.dashboard.list-row :value="$entry->is_today ? 'Today!' : 'in '.$entry->days_until.' '.\Illuminate\Support\Str::plural('day', $entry->days_until)">
                <span @if ($entry->is_today) style="font-weight: 700; color: var(--pw-card-accent);" @else style="color: var(--pw-card-text);" @endif>
                    🎂 {{ $entry->customer_name }}
                </span>
                <span style="color: var(--pw-card-text-muted); margin-left: 6px;">{{ $entry->birthday_date }}</span>
            </x-admin.dashboard.list-row>
        @endforeach
    @endif
</x-admin.dashboard.preview-card>
