<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Month Navigation --}}
        <div class="flex items-center justify-between">
            <x-filament::button color="gray" wire:click="previousMonth" icon="heroicon-o-chevron-left">
                Previous
            </x-filament::button>

            <h2 class="text-xl font-bold text-gray-800 dark:text-gray-200">
                {{ $this->monthLabel }}
            </h2>

            <x-filament::button color="gray" wire:click="nextMonth" icon-position="after" icon="heroicon-o-chevron-right">
                Next
            </x-filament::button>
        </div>

        {{-- Calendar Grid --}}
        <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 overflow-hidden shadow-sm">
            {{-- Day Headers --}}
            <div class="grid grid-cols-7 bg-amber-50 dark:bg-amber-950/30 border-b border-gray-200 dark:border-gray-700">
                @foreach (['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $dayName)
                    <div class="px-2 py-3 text-center text-sm font-semibold text-amber-800 dark:text-amber-300">
                        {{ $dayName }}
                    </div>
                @endforeach
            </div>

            {{-- Days --}}
            <div class="grid grid-cols-7">
                @foreach ($this->calendarDays as $day)
                    @if ($day === null)
                        <div class="min-h-[100px] border-b border-r border-gray-100 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-950/30"></div>
                    @else
                        <div
                            wire:click="selectDay('{{ $day['date'] }}')"
                            class="min-h-[100px] border-b border-r border-gray-100 dark:border-gray-800 p-2 cursor-pointer transition-colors hover:bg-amber-50/50 dark:hover:bg-amber-950/20
                                {{ $day['isToday'] ? 'bg-amber-50 dark:bg-amber-950/40' : '' }}
                                {{ $selectedDate === $day['date'] ? 'ring-2 ring-inset ring-amber-400' : '' }}"
                        >
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-sm font-medium {{ $day['isToday'] ? 'text-amber-700 dark:text-amber-400 font-bold' : 'text-gray-700 dark:text-gray-300' }}">
                                    {{ $day['day'] }}
                                </span>
                                @if (count($day['posts']) > 0)
                                    <span class="text-xs bg-amber-100 dark:bg-amber-900 text-amber-700 dark:text-amber-300 rounded-full px-1.5 py-0.5 font-medium">
                                        {{ count($day['posts']) }}
                                    </span>
                                @endif
                            </div>

                            {{-- Platform Icons --}}
                            <div class="flex flex-wrap gap-1">
                                @foreach ($day['posts'] as $post)
                                    @php
                                        $colors = match($post['platform']) {
                                            'instagram' => 'bg-pink-100 text-pink-600 dark:bg-pink-900/50 dark:text-pink-400',
                                            'facebook' => 'bg-blue-100 text-blue-600 dark:bg-blue-900/50 dark:text-blue-400',
                                            'tiktok' => 'bg-gray-800 text-white dark:bg-gray-600',
                                            default => 'bg-gray-100 text-gray-600',
                                        };
                                        $icon = match($post['platform']) {
                                            'instagram' => '📸',
                                            'facebook' => '📘',
                                            'tiktok' => '🎵',
                                            default => '📝',
                                        };
                                    @endphp
                                    <span class="inline-flex items-center rounded px-1 py-0.5 text-xs {{ $colors }}">
                                        {{ $icon }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>

        {{-- Selected Day Detail --}}
        @if ($selectedDate)
            <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-6 shadow-sm">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">
                    {{ \Carbon\Carbon::parse($selectedDate)->format('l, F j, Y') }}
                </h3>

                @if (count($selectedDayPosts) > 0)
                    <div class="space-y-3">
                        @foreach ($selectedDayPosts as $post)
                            @php
                                $platformColors = match($post['platform']) {
                                    'instagram' => 'border-l-pink-500 bg-pink-50/50 dark:bg-pink-950/20',
                                    'facebook' => 'border-l-blue-500 bg-blue-50/50 dark:bg-blue-950/20',
                                    'tiktok' => 'border-l-gray-800 bg-gray-50 dark:bg-gray-800/30',
                                    default => 'border-l-gray-300',
                                };
                                $statusColors = match($post['status']) {
                                    'draft' => 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400',
                                    'scheduled' => 'bg-amber-100 text-amber-700 dark:bg-amber-900 dark:text-amber-300',
                                    'posted' => 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300',
                                    default => '',
                                };
                            @endphp
                            <div class="border-l-4 rounded-r-lg p-4 {{ $platformColors }}">
                                <div class="flex items-center justify-between mb-2">
                                    <div class="flex items-center gap-2">
                                        <span class="font-medium text-gray-800 dark:text-gray-200">
                                            {{ match($post['platform']) { 'instagram' => '📸 Instagram', 'facebook' => '📘 Facebook', 'tiktok' => '🎵 TikTok', default => $post['platform'] } }}
                                        </span>
                                        <span class="text-sm text-gray-500">{{ $post['time'] }}</span>
                                    </div>
                                    <span class="text-xs rounded-full px-2 py-1 font-medium {{ $statusColors }}">
                                        {{ ucfirst($post['status']) }}
                                    </span>
                                </div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">{{ $post['caption'] }}</p>
                                @if ($post['product'])
                                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Product: {{ $post['product'] }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500 dark:text-gray-400 text-sm">No posts scheduled for this day.</p>
                @endif
            </div>
        @endif
    </div>
</x-filament-panels::page>
