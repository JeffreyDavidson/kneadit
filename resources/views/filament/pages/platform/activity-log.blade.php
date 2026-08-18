<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Filters (Filament Schema) --}}
        {{ $this->content }}

        <div class="flex justify-end">
            <button wire:click="resetFilters" class="text-sm text-brand-300 hover:text-brand-200 font-medium">
                Reset Filters
            </button>
        </div>

        {{-- Activity Table --}}
        <div class="bg-brand-800 border border-brand-700/60 rounded-xl overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-brand-900/50 border-b border-brand-700/60">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold text-brand-300 uppercase text-xs tracking-wider">Time</th>
                        <th class="px-4 py-3 text-left font-semibold text-brand-300 uppercase text-xs tracking-wider">User</th>
                        <th class="px-4 py-3 text-left font-semibold text-brand-300 uppercase text-xs tracking-wider">Action</th>
                        <th class="px-4 py-3 text-left font-semibold text-brand-300 uppercase text-xs tracking-wider">Description</th>
                        <th class="px-4 py-3 text-left font-semibold text-brand-300 uppercase text-xs tracking-wider">IP</th>
                        <th class="px-4 py-3 text-center font-semibold text-brand-300 uppercase text-xs tracking-wider">Details</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-brand-700/40">
                    @forelse ($this->activities as $activity)
                        <tr class="hover:bg-brand-300/5 transition-colors">
                            <td class="px-4 py-3 text-brand-400 whitespace-nowrap">
                                {{ $activity->created_at?->format('M d, Y H:i') }}
                            </td>
                            <td class="px-4 py-3 font-medium text-white">
                                {{ $activity->user_name }}
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold text-white whitespace-nowrap {{ $activity->action->pillClass() }}">
                                    {{ $activity->action->getLabel() }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-brand-100">
                                {{ $activity->description }}
                            </td>
                            <td class="px-4 py-3 text-brand-400 text-xs font-mono">
                                {{ $activity->ip_address }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if ($activity->properties)
                                    <button wire:click="toggleExpanded({{ $activity->id }})" class="text-brand-300 hover:text-brand-200">
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
                                <td colspan="6" class="px-4 py-3 bg-brand-900/40">
                                    <div class="text-xs font-mono space-y-1">
                                        <p class="font-semibold text-brand-300 mb-2">Changes:</p>
                                        @php $props = is_array($activity->properties) ? $activity->properties : json_decode($activity->properties, true); @endphp
                                        @if (isset($props['changes']))
                                            @foreach ($props['changes'] as $field => $newValue)
                                                <div class="flex gap-2">
                                                    <span class="font-medium text-brand-100">{{ $field }}:</span>
                                                    <span class="text-brand-300">{{ is_array($newValue) ? json_encode($newValue) : $newValue }}</span>
                                                </div>
                                            @endforeach
                                        @else
                                            <pre class="text-brand-200">{{ json_encode($props, JSON_PRETTY_PRINT) }}</pre>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endif
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-12 text-center text-brand-400">
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
                <div class="px-4 py-3 border-t border-brand-700/60 flex items-center justify-between">
                    <p class="text-sm text-brand-400">
                        Showing {{ $this->activities->firstItem() }}–{{ $this->activities->lastItem() }} of {{ $this->activities->total() }}
                    </p>
                    <div class="flex gap-2">
                        <button wire:click="previousPage" @disabled($this->activities->onFirstPage()) class="px-3 py-1 rounded-lg border border-brand-700/60 text-brand-200 text-sm disabled:opacity-50 disabled:cursor-not-allowed hover:bg-brand-300/5 hover:border-brand-300/40">
                            Previous
                        </button>
                        <button wire:click="nextPage" @disabled(! $this->activities->hasMorePages()) class="px-3 py-1 rounded-lg border border-brand-700/60 text-brand-200 text-sm disabled:opacity-50 disabled:cursor-not-allowed hover:bg-brand-300/5 hover:border-brand-300/40">
                            Next
                        </button>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-filament-panels::page>
