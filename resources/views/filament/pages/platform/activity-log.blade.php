<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Filters --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Action</label>
                    <select wire:model.live="filterAction" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
                        <option value="">All Actions</option>
                        @foreach ($this->actionTypes as $action)
                            <option value="{{ $action->value }}">{{ $action->getLabel() }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Model Type</label>
                    <select wire:model.live="filterModelType" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
                        <option value="">All Types</option>
                        @foreach ($this->modelTypes as $type)
                            <option value="{{ $type['value'] }}">{{ $type['label'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">User</label>
                    <input type="text" wire:model.live.debounce.300ms="filterUser" placeholder="Search by user..." class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">From</label>
                    <input type="date" wire:model.live="filterDateFrom" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">To</label>
                    <input type="date" wire:model.live="filterDateTo" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
                </div>
            </div>
            <div class="mt-3 flex justify-end">
                <button wire:click="resetFilters" class="text-sm text-primary-600 hover:text-primary-800 font-medium">
                    Reset Filters
                </button>
            </div>
        </div>

        {{-- Activity Table --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-300">Time</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-300">User</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-300">Action</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-300">Description</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-300">IP</th>
                        <th class="px-4 py-3 text-center font-medium text-gray-600 dark:text-gray-300">Details</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse ($this->activities as $activity)
                        <tr class="hover:bg-amber-50/50 dark:hover:bg-gray-750 transition-colors">
                            <td class="px-4 py-3 text-gray-500 dark:text-gray-400 whitespace-nowrap">
                                {{ $activity->created_at?->format('M d, Y H:i') }}
                            </td>
                            <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">
                                {{ $activity->user_name }}
                            </td>
                            <td class="px-4 py-3">
                                @php
                                    $colors = [
                                        'created' => 'bg-emerald-100 text-emerald-800',
                                        'updated' => 'bg-sky-100 text-sky-800',
                                        'deleted' => 'bg-rose-100 text-rose-800',
                                        'status_changed' => 'bg-amber-100 text-amber-800',
                                        'login' => 'bg-violet-100 text-violet-800',
                                    ];
                                    $color = $colors[$activity->action->value] ?? 'bg-gray-100 text-gray-800';
                                @endphp
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $color }}">
                                    {{ $activity->action->getLabel() }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-gray-700 dark:text-gray-300">
                                {{ $activity->description }}
                            </td>
                            <td class="px-4 py-3 text-gray-400 text-xs">
                                {{ $activity->ip_address }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if ($activity->properties)
                                    <button wire:click="toggleExpanded({{ $activity->id }})" class="text-primary-600 hover:text-primary-800">
                                        @if ($expandedId === $activity->id)
                                            <x-heroicon-o-chevron-up class="w-4 h-4" />
                                        @else
                                            <x-heroicon-o-chevron-down class="w-4 h-4" />
                                        @endif
                                    </button>
                                @endif
                            </td>
                        </tr>
                        @if ($expandedId === $activity->id && $activity->properties)
                            <tr>
                                <td colspan="6" class="px-4 py-3 bg-gray-50 dark:bg-gray-900">
                                    <div class="text-xs font-mono space-y-1">
                                        <p class="font-semibold text-gray-600 dark:text-gray-400 mb-2">Changes:</p>
                                        @php $props = is_array($activity->properties) ? $activity->properties : json_decode($activity->properties, true); @endphp
                                        @if (isset($props['changes']))
                                            @foreach ($props['changes'] as $field => $newValue)
                                                <div class="flex gap-2">
                                                    <span class="font-medium text-gray-700 dark:text-gray-300">{{ $field }}:</span>
                                                    <span class="text-primary-600">{{ is_array($newValue) ? json_encode($newValue) : $newValue }}</span>
                                                </div>
                                            @endforeach
                                        @else
                                            <pre class="text-gray-600">{{ json_encode($props, JSON_PRETTY_PRINT) }}</pre>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endif
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-12 text-center text-gray-400">
                                <div class="flex flex-col items-center gap-2">
                                    <x-heroicon-o-clipboard-document-list class="w-8 h-8" />
                                    <p>No activity logs found.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            {{-- Pagination --}}
            @if ($this->activities->hasPages())
                <div class="px-4 py-3 border-t border-gray-100 dark:border-gray-700 flex items-center justify-between">
                    <p class="text-sm text-gray-500">
                        Showing {{ $this->activities->firstItem() }}–{{ $this->activities->lastItem() }} of {{ $this->activities->total() }}
                    </p>
                    <div class="flex gap-2">
                        <button wire:click="previousPage" @disabled(!$this->activities->onFirstPage()) class="px-3 py-1 rounded-lg border text-sm disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50">
                            Previous
                        </button>
                        <button wire:click="nextPage" @disabled(!$this->activities->hasMorePages()) class="px-3 py-1 rounded-lg border text-sm disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50">
                            Next
                        </button>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-filament-panels::page>
