<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <h2 class="text-2xl font-bold text-gray-900">Holiday Planning Calendar</h2>
                <div class="text-sm text-gray-500">
                    Planning ahead for seasonal orders and special treats
                </div>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Urgent Holidays -->
            <div class="bg-red-50 rounded-lg shadow p-6 border border-red-200">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 15.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-red-600">Needs Attention</p>
                        <p class="text-2xl font-bold text-red-900">
                            {{ $inPrepPeriod->count() }}
                        </p>
                        <p class="text-sm text-red-600">holidays in prep period</p>
                    </div>
                </div>
            </div>

            <!-- Upcoming Holidays -->
            <div class="bg-blue-50 rounded-lg shadow p-6 border border-blue-200">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-blue-600">Upcoming</p>
                        <p class="text-2xl font-bold text-blue-900">
                            {{ $upcomingHolidays->count() }}
                        </p>
                        <p class="text-sm text-blue-600">holidays to plan for</p>
                    </div>
                </div>
            </div>

            <!-- Total Holidays -->
            <div class="bg-green-50 rounded-lg shadow p-6 border border-green-200">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 0v1m0 0V9a2 2 0 11-2 0V8ZM6 10v8a2 2 0 002 2h8a2 2 0 002-2v-8a2 2 0 00-2-2H8a2 2 0 00-2 2Z"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-green-600">Total Holidays</p>
                        <p class="text-2xl font-bold text-green-900">
                            {{ $holidays->count() }}
                        </p>
                        <p class="text-sm text-green-600">in calendar</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Urgent Holidays Alert -->
        @if ($inPrepPeriod->isNotEmpty())
            <div class="bg-red-100 border-l-4 border-red-500 p-4 rounded-lg">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-red-800">Action Required!</h3>
                        <div class="mt-2 text-sm text-red-700">
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
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-xl font-bold text-gray-900 mb-6">Holiday Calendar</h3>

            @foreach ($this->getHolidaysByMonth() as $monthKey => $monthHolidays)
                <div class="mb-8 last:mb-0">
                    <h4 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        {{ \Carbon\Carbon::createFromFormat('Y-m', $monthKey)->format('F Y') }}
                    </h4>

                    <div class="space-y-3">
                        @foreach ($monthHolidays as $holiday)
                            @php
                                $statusColor = $this->getStatusColor($holiday);
                                $statusText = $this->getStatusText($holiday);
                                $daysAway = $this->getDaysAway($holiday);
                            @endphp

                            <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                                <div class="flex justify-between items-start">
                                    <div class="flex-1">
                                        <div class="flex items-center space-x-3 mb-2">
                                            <h5 class="text-lg font-semibold text-gray-900">{{ $holiday->name }}</h5>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                {{ $statusColor === 'red' ? 'bg-red-100 text-red-800' : '' }}
                                                {{ $statusColor === 'yellow' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                                {{ $statusColor === 'orange' ? 'bg-orange-100 text-orange-800' : '' }}
                                                {{ $statusColor === 'green' ? 'bg-green-100 text-green-800' : '' }}
                                                {{ $statusColor === 'gray' ? 'bg-gray-100 text-gray-800' : '' }}">
                                                {{ $statusText }}
                                            </span>
                                        </div>

                                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm text-gray-600">
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
                                                {{ $holiday->start_prep_by->format('F j, Y') }}
                                            </div>
                                        </div>

                                        @if ($holiday->notes)
                                            <div class="mt-3 p-3 bg-gray-50 rounded-md">
                                                <p class="text-sm text-gray-700">
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
        <div class="bg-blue-50 rounded-lg shadow p-6 border border-blue-200">
            <h3 class="text-lg font-semibold text-blue-900 mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Holiday Planning Tips
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-blue-800">
                <div>
                    <h4 class="font-semibold mb-2">Preparation Timeline:</h4>
                    <ul class="space-y-1 list-disc pl-5">
                        <li>Start marketing 3-4 weeks before the holiday</li>
                        <li>Begin ingredient sourcing during the "prep period"</li>
                        <li>Schedule extra staff for high-volume holidays</li>
                        <li>Create special holiday menu items</li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-semibold mb-2">Order Management:</h4>
                    <ul class="space-y-1 list-disc pl-5">
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