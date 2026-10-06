<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Header -->
        <div class="rounded-lg bg-(--kn-surface) p-6 shadow">
            <div class="flex items-center justify-between">
                <h2 class="text-2xl font-bold text-(--kn-ink)">Holiday Planning Calendar</h2>
                <div class="text-sm text-(--kn-muted)">Planning ahead for seasonal orders and special treats</div>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
            <!-- Urgent Holidays -->
            <div class="rounded-lg border border-(--kn-danger) bg-(--kn-danger-tint) p-6 shadow">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <x-heroicon-o-exclamation-triangle class="h-8 w-8 text-(--kn-danger)" stroke-width="2" />
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-(--kn-danger)">Needs Attention</p>
                        <p class="text-2xl font-bold text-(--kn-danger)">{{ $inPrepPeriod->count() }}</p>
                        <p class="text-sm text-(--kn-danger)">holidays in prep period</p>
                    </div>
                </div>
            </div>

            <!-- Upcoming Holidays -->
            <div class="rounded-lg border border-(--kn-info) bg-(--kn-info-tint) p-6 shadow">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <x-heroicon-o-calendar class="h-8 w-8 text-(--kn-info)" stroke-width="2" />
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-(--kn-info)">Upcoming</p>
                        <p class="text-2xl font-bold text-(--kn-info)">{{ $upcomingHolidays->count() }}</p>
                        <p class="text-sm text-(--kn-info)">holidays to plan for</p>
                    </div>
                </div>
            </div>

            <!-- Total Holidays -->
            <div class="rounded-lg border border-(--kn-success) bg-(--kn-success-tint) p-6 shadow">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <x-heroicon-o-gift class="h-8 w-8 text-(--kn-success)" stroke-width="2" />
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-(--kn-success)">Total Holidays</p>
                        <p class="text-2xl font-bold text-(--kn-success)">{{ $holidays->count() }}</p>
                        <p class="text-sm text-(--kn-success)">in calendar</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Urgent Holidays Alert -->
        @if ($inPrepPeriod->isNotEmpty())
            <div class="rounded-lg border-l-4 border-red-500 bg-(--kn-danger-tint) p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <x-heroicon-s-x-circle class="h-5 w-5 text-(--kn-danger)" />
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-(--kn-danger)">Action Required!</h3>
                        <div class="mt-2 text-sm text-(--kn-danger)">
                            <p>The following holidays are in their preparation period:</p>
                            <ul class="mt-1 list-disc pl-5">
                                @foreach ($inPrepPeriod as $holiday)
                                    <li>
                                        <strong>{{ $holiday->name }}</strong>
                                        ({{ $this->getDaysAway($holiday) }})
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Holiday Calendar by Month -->
        <div class="rounded-lg bg-(--kn-surface) p-6 shadow">
            <h3 class="mb-6 text-xl font-bold text-(--kn-ink)">Holiday Calendar</h3>

            @foreach ($this->getHolidaysByMonth() as $monthKey => $monthHolidays)
                <div class="mb-8 last:mb-0">
                    <h4 class="mb-4 flex items-center text-lg font-semibold text-(--kn-ink-2)">
                        <x-heroicon-o-calendar class="text-primary-600 mr-2 h-5 w-5" stroke-width="2" />
                        {{ \Carbon\Carbon::createFromFormat('Y-m', $monthKey)->format('F Y') }}
                    </h4>

                    <div class="space-y-3">
                        @foreach ($monthHolidays as $holiday)
                            @php
                                $presenter = \App\Presenters\HolidayPresenter::for($holiday);
                                $statusColor = $presenter->prepStatusColor();
                                $statusText = $presenter->prepStatus();
                                $daysAway = $presenter->daysAwayLabel();
                            @endphp

                            <div class="rounded-lg border border-(--kn-border) p-4 transition-shadow hover:shadow-md">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <div class="mb-2 flex items-center space-x-3">
                                            <h5 class="text-lg font-semibold text-(--kn-ink)">{{ $holiday->name }}</h5>
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                {{ $statusColor === 'red' ? 'bg-(--kn-danger-tint) text-(--kn-danger)' : '' }}
                                                {{ $statusColor === 'yellow' ? 'bg-(--kn-warning-tint) text-(--kn-warning)' : '' }}
                                                {{ $statusColor === 'orange' ? 'bg-(--kn-warning-tint) text-(--kn-warning)' : '' }}
                                                {{ $statusColor === 'green' ? 'bg-(--kn-success-tint) text-(--kn-success)' : '' }}
                                                {{ $statusColor === 'gray' ? 'bg-(--kn-surface-sunken) text-(--kn-ink-2)' : '' }}"
                                            >
                                                {{ $statusText }}
                                            </span>
                                        </div>

                                        <div class="grid grid-cols-1 gap-4 text-sm text-(--kn-muted) md:grid-cols-3">
                                            <div>
                                                <span class="font-medium">Date:</span>
                                                {{ $holiday->date->format('F j, Y') }} ({{ $holiday->date->format('l') }})
                                            </div>
                                            <div>
                                                <span class="font-medium">Status:</span>
                                                {{ $daysAway }}
                                            </div>
                                            <div>
                                                <span class="font-medium">Start prep by:</span>
                                                {{ $presenter->startPrepBy()->format('F j, Y') }}
                                            </div>
                                        </div>

                                        @if ($holiday->notes)
                                            <div class="mt-3 rounded-md bg-(--kn-surface-sunken) p-3">
                                                <p class="text-sm text-(--kn-ink-2)">
                                                    <span class="font-medium">Notes:</span> {{ $holiday->notes }}
                                                </p>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Tips Section -->
        <div class="rounded-lg border border-(--kn-info) bg-(--kn-info-tint) p-6 shadow">
            <h3 class="mb-4 flex items-center text-lg font-semibold text-(--kn-info)">
                <x-heroicon-o-information-circle class="mr-2 h-5 w-5" stroke-width="2" />
                Holiday Planning Tips
            </h3>
            <div class="grid grid-cols-1 gap-4 text-sm text-(--kn-info) md:grid-cols-2">
                <div>
                    <h4 class="mb-2 font-semibold">Preparation Timeline:</h4>
                    <ul class="list-disc space-y-1 pl-5">
                        <li>Start marketing 3-4 weeks before the holiday</li>
                        <li>Begin ingredient sourcing during the "prep period"</li>
                        <li>Schedule extra staff for high-volume holidays</li>
                        <li>Create special holiday menu items</li>
                    </ul>
                </div>
                <div>
                    <h4 class="mb-2 font-semibold">Order Management:</h4>
                    <ul class="list-disc space-y-1 pl-5">
                        <li>Set order cutoff dates to manage capacity</li>
                        <li>Consider pre-order discounts for early bookings</li>
                        <li>Plan for increased delivery demand</li>
                        <li>Stock up on holiday-themed packaging</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
