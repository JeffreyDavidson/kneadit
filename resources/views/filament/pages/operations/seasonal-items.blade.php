<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Add Form --}}
        <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="fi-section-content p-6">
                {{ $this->content }}
            </div>
        </div>

        {{-- Current Seasonal Items --}}
        <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="fi-section-content p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                    🟢 Currently Available
                </h3>
                @forelse ($this->currentItems as $item)
                    <div class="flex items-center justify-between py-3 border-b border-gray-100 dark:border-gray-800 last:border-0">
                        <div>
                            <span class="font-medium text-gray-900 dark:text-white">{{ $item->product->name }}</span>
                            <span class="text-sm text-gray-500 ml-2">
                                {{ $item->available_from->format('M j') }} – {{ $item->available_until->format('M j, Y') }}
                            </span>
                            @if ($item->notes)
                                <span class="text-sm text-gray-400 ml-2">· {{ $item->notes }}</span>
                            @endif
                        </div>
                        <button wire:click="deleteSeasonalItem({{ $item->id }})" class="text-red-500 hover:text-red-700 text-sm">Remove</button>
                    </div>
                @empty
                    <p class="text-gray-500 text-sm">No seasonal items currently available.</p>
                @endforelse
            </div>
        </div>

        {{-- Upcoming --}}
        <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="fi-section-content p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                    🔵 Upcoming
                </h3>
                @forelse ($this->upcomingItems as $item)
                    <div class="flex items-center justify-between py-3 border-b border-gray-100 dark:border-gray-800 last:border-0">
                        <div>
                            <span class="font-medium text-gray-900 dark:text-white">{{ $item->product->name }}</span>
                            <span class="text-sm text-gray-500 ml-2">
                                Starts {{ $item->available_from->format('M j, Y') }} – {{ $item->available_until->format('M j, Y') }}
                            </span>
                            @if ($item->notes)
                                <span class="text-sm text-gray-400 ml-2">· {{ $item->notes }}</span>
                            @endif
                        </div>
                        <button wire:click="deleteSeasonalItem({{ $item->id }})" class="text-red-500 hover:text-red-700 text-sm">Remove</button>
                    </div>
                @empty
                    <p class="text-gray-500 text-sm">No upcoming seasonal items.</p>
                @endforelse
            </div>
        </div>

        {{-- Expired --}}
        <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="fi-section-content p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                    ⚫ Expired
                </h3>
                @forelse ($this->expiredItems as $item)
                    <div class="flex items-center justify-between py-3 border-b border-gray-100 dark:border-gray-800 last:border-0">
                        <div>
                            <span class="font-medium text-gray-500">{{ $item->product->name }}</span>
                            <span class="text-sm text-gray-400 ml-2">
                                {{ $item->available_from->format('M j') }} – {{ $item->available_until->format('M j, Y') }}
                            </span>
                        </div>
                        <button wire:click="deleteSeasonalItem({{ $item->id }})" class="text-red-500 hover:text-red-700 text-sm">Remove</button>
                    </div>
                @empty
                    <p class="text-gray-500 text-sm">No expired seasonal items.</p>
                @endforelse
            </div>
        </div>

        {{-- Timeline --}}
        <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="fi-section-content p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">📅 Timeline</h3>
                @php
                    $allItems = \App\Models\Inventory\SeasonalItem::with('product')
                        ->orderBy('available_from')
                        ->get();
                    $now = now();
                @endphp
                @forelse ($allItems as $item)
                    @php
                        $isActive = $item->is_currently_available;
                        $isPast = $item->available_until < $now;
                    @endphp
                    <div class="flex items-center gap-3 py-2">
                        <div class="w-3 h-3 rounded-full {{ $isActive ? 'bg-green-500' : ($isPast ? 'bg-gray-300' : 'bg-blue-500') }}"></div>
                        <span class="text-sm font-medium {{ $isPast ? 'text-gray-400' : 'text-gray-900 dark:text-white' }}">
                            {{ $item->product->name }}
                        </span>
                        <span class="text-xs text-gray-500">
                            {{ $item->available_from->format('M j') }} → {{ $item->available_until->format('M j, Y') }}
                        </span>
                    </div>
                @empty
                    <p class="text-gray-500 text-sm">No seasonal items to display.</p>
                @endforelse
            </div>
        </div>
    </div>
</x-filament-panels::page>
