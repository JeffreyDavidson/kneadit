<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Month Navigation --}}
        <div class="flex items-center justify-between">
            <x-filament::button color="gray" wire:click="previousMonth" icon="heroicon-o-chevron-left">
                Previous
            </x-filament::button>

            <h2 class="text-xl font-bold text-(--kn-ink-2)">{{ $this->monthLabel }}</h2>

            <x-filament::button
                color="gray"
                wire:click="nextMonth"
                icon-position="after"
                icon="heroicon-o-chevron-right"
            >
                Next
            </x-filament::button>
        </div>

        {{-- Calendar Grid --}}
        <div class="overflow-hidden rounded-xl border border-(--kn-border) bg-(--kn-surface) shadow-sm">
            {{-- Day Headers --}}
            <div class="grid grid-cols-7 border-b border-(--kn-border) bg-(--kn-warning-tint) dark:bg-amber-950/30">
                @foreach (['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $dayName)
                    <div class="px-2 py-3 text-center text-sm font-semibold text-(--kn-warning) dark:text-amber-300">
                        {{ $dayName }}
                    </div>
                @endforeach
            </div>

            {{-- Days --}}
            <div class="grid grid-cols-7">
                @foreach ($this->calendarDays as $day)
                    @if ($day === null)
                        <div class="min-h-[100px] border-r border-b border-(--kn-border) bg-gray-50/50 dark:bg-gray-950/30"></div>
                    @else
                        <div
                            wire:click="selectDay('{{ $day['date'] }}')"
                            class="min-h-[100px] border-b border-r border-(--kn-border) p-2 cursor-pointer transition-colors hover:bg-amber-50/50 dark:hover:bg-amber-950/20
                                {{ $day['isToday'] ? 'bg-(--kn-warning-tint) dark:bg-amber-950/40' : '' }}
                                {{ $selectedDate === $day['date'] ? 'ring-2 ring-inset ring-amber-400' : '' }}"
                        >
                            <div class="mb-1 flex items-center justify-between">
                                <span class="text-sm font-medium {{ $day['isToday'] ? 'text-(--kn-warning) dark:text-amber-400 font-bold' : 'text-(--kn-ink-2)' }}">
                                    {{ $day['day'] }}
                                </span>
                                @if (count($day['posts']) > 0)
                                    <span class="rounded-full bg-(--kn-warning-tint) px-1.5 py-0.5 text-xs font-medium text-(--kn-warning) dark:bg-amber-900 dark:text-amber-300">
                                        {{ count($day['posts']) }}
                                    </span>
                                @endif
                            </div>

                            {{-- Platform Icons --}}
                            <div class="flex flex-wrap gap-1">
                                @foreach ($day['posts'] as $post)
                                    @php
                                        $colors = match ($post['platform']) {
                                            'instagram' => 'bg-pink-100 text-pink-600 dark:bg-pink-900/50 dark:text-pink-400',
                                            'facebook' => 'bg-(--kn-info-tint) text-(--kn-info) dark:bg-blue-900/50 dark:text-blue-400',
                                            'tiktok' => 'bg-(--kn-espresso) text-(--kn-on-espresso)',
                                            default => 'bg-(--kn-surface-sunken) text-(--kn-muted)',
                                        };
                                        $icon = match ($post['platform']) {
                                            'instagram' => 'heroicon-o-camera',
                                            'facebook' => 'heroicon-o-share',
                                            'tiktok' => 'heroicon-o-musical-note',
                                            default => 'heroicon-o-pencil-square',
                                        };
                                    @endphp
                                    <span class="inline-flex items-center rounded px-1 py-0.5 text-xs {{ $colors }}">
                                        <x-filament::icon :icon="$icon" class="h-3.5 w-3.5" />
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
            <div class="rounded-xl border border-(--kn-border) bg-(--kn-surface) p-6 shadow-sm">
                <h3 class="mb-4 text-lg font-semibold text-(--kn-ink-2)">
                    {{ \Carbon\Carbon::parse($selectedDate)->format('l, F j, Y') }}
                </h3>

                @if (count($selectedDayPosts) > 0)
                    <div class="space-y-3">
                        @foreach ($selectedDayPosts as $post)
                            @php
                                $platformColors = match ($post['platform']) {
                                    'instagram' => 'border-l-pink-500 bg-pink-50/50 dark:bg-pink-950/20',
                                    'facebook' => 'border-l-blue-500 bg-blue-50/50 dark:bg-blue-950/20',
                                    'tiktok' => 'border-l-gray-800 bg-(--kn-surface-sunken) dark:bg-gray-800/30',
                                    default => 'border-l-gray-300',
                                };
                                $statusColors = match ($post['status']) {
                                    'draft' => 'bg-(--kn-surface-sunken) text-(--kn-muted)',
                                    'scheduled' => 'bg-(--kn-warning-tint) text-(--kn-warning) dark:bg-amber-900 dark:text-amber-300',
                                    'posted' => 'bg-(--kn-success-tint) text-(--kn-success) dark:bg-green-900 dark:text-green-300',
                                    default => '',
                                };
                            @endphp
                            <div class="border-l-4 rounded-r-lg p-4 {{ $platformColors }}">
                                <div class="mb-2 flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        @php
                                            $platformIcon = match ($post['platform']) {
                                                'instagram' => 'heroicon-o-camera',
                                                'facebook' => 'heroicon-o-share',
                                                'tiktok' => 'heroicon-o-musical-note',
                                                default => 'heroicon-o-pencil-square',
                                            };
                                            $platformLabel = match ($post['platform']) {
                                                'instagram' => 'Instagram',
                                                'facebook' => 'Facebook',
                                                'tiktok' => 'TikTok',
                                                default => $post['platform'],
                                            };
                                        @endphp
                                        <span class="inline-flex items-center gap-1.5 font-medium text-(--kn-ink-2)">
                                            <x-filament::icon :icon="$platformIcon" class="h-4 w-4" />
                                            {{ $platformLabel }}
                                        </span>
                                        <span class="text-sm text-(--kn-muted)">{{ $post['time'] }}</span>
                                    </div>
                                    <span class="text-xs rounded-full px-2 py-1 font-medium {{ $statusColors }}">
                                        {{ ucfirst($post['status']) }}
                                    </span>
                                </div>
                                <p class="text-sm text-(--kn-muted)">{{ $post['caption'] }}</p>
                                @if ($post['product'])
                                    <p class="mt-1 text-xs text-(--kn-muted)">Product: {{ $post['product'] }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-(--kn-muted)">No posts scheduled for this day.</p>
                @endif
            </div>
        @endif
    </div>
</x-filament-panels::page>
