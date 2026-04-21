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
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: 16px; height: 16px;"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 15.75 7.5-7.5 7.5 7.5" /></svg>
                                        @else
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: 16px; height: 16px;"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" /></svg>
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
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: 32px; height: 32px;"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15a2.25 2.25 0 0 1 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z" /></svg>
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
