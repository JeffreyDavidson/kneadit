<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Filters (Filament Schema) --}}
        {{ $this->content }}

        <div class="flex justify-end">
            <button wire:click="resetFilters" class="text-brand-300 hover:text-brand-200 text-sm font-medium">
                Reset Filters
            </button>
        </div>

        {{-- Activity Table --}}
        <div class="bg-brand-800 border-brand-700/60 overflow-hidden rounded-xl border">
            <table class="w-full text-sm">
                <thead class="bg-brand-900/50 border-brand-700/60 border-b">
                    <tr>
                        <th class="text-brand-300 px-4 py-3 text-left text-xs font-semibold tracking-wider uppercase">
                            Time
                        </th>
                        <th class="text-brand-300 px-4 py-3 text-left text-xs font-semibold tracking-wider uppercase">
                            User
                        </th>
                        <th class="text-brand-300 px-4 py-3 text-left text-xs font-semibold tracking-wider uppercase">
                            Action
                        </th>
                        <th class="text-brand-300 px-4 py-3 text-left text-xs font-semibold tracking-wider uppercase">
                            Description
                        </th>
                        <th class="text-brand-300 px-4 py-3 text-left text-xs font-semibold tracking-wider uppercase">
                            IP
                        </th>
                        <th class="text-brand-300 px-4 py-3 text-center text-xs font-semibold tracking-wider uppercase">
                            Details
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-brand-700/40 divide-y">
                    @forelse ($this->activities as $activity)
                        <tr class="hover:bg-brand-300/5 transition-colors">
                            <td class="text-brand-400 px-4 py-3 whitespace-nowrap">
                                {{ $activity->created_at?->format('M d, Y H:i') }}
                            </td>
                            <td class="px-4 py-3 font-medium text-white">{{ $activity->user_name }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold text-white whitespace-nowrap {{ $activity->action->pillClass() }}">
                                    {{ $activity->action->getLabel() }}
                                </span>
                            </td>
                            <td class="text-brand-100 px-4 py-3">{{ $activity->description }}</td>
                            <td class="text-brand-400 px-4 py-3 font-mono text-xs">{{ $activity->ip_address }}</td>
                            <td class="px-4 py-3 text-center">
                                @if ($activity->properties)
                                    <button
                                        wire:click="toggleExpanded({{ $activity->id }})"
                                        class="text-brand-300 hover:text-brand-200"
                                    >
                                        @if ($expandedId === $activity->id)
                                            <x-heroicon-o-chevron-up class="h-4 w-4" />
                                        @else
                                            <x-heroicon-o-chevron-down class="h-4 w-4" />
                                        @endif
                                    </button>
                                @endif
                            </td>
                        </tr>
                        @if ($expandedId === $activity->id && $activity->properties)
                            <tr>
                                <td colspan="6" class="bg-brand-900/40 px-4 py-3">
                                    <div class="space-y-1 font-mono text-xs">
                                        <p class="text-brand-300 mb-2 font-semibold">Changes:</p>
                                        @php $props = is_array($activity->properties) ? $activity->properties : json_decode($activity->properties, true); @endphp
                                        @if (isset($props['changes']))
                                            @foreach ($props['changes'] as $field => $newValue)
                                                <div class="flex gap-2">
                                                    <span class="text-brand-100 font-medium">{{ $field }}:</span>
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
                            <td colspan="6" class="text-brand-400 px-4 py-12 text-center">
                                <div class="flex flex-col items-center gap-2">
                                    <x-heroicon-o-clipboard-document-list class="h-8 w-8" />
                                    <p>No activity logs found.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            {{-- Pagination --}}
            @if ($this->activities->hasPages())
                <div class="border-brand-700/60 flex items-center justify-between border-t px-4 py-3">
                    <p class="text-brand-400 text-sm">
                        Showing {{ $this->activities->firstItem() }}–{{ $this->activities->lastItem() }} of {{ $this->activities->total() }}
                    </p>
                    <div class="flex gap-2">
                        <button
                            wire:click="previousPage"
                            @disabled($this->activities->onFirstPage())
                            class="border-brand-700/60 text-brand-200 hover:bg-brand-300/5 hover:border-brand-300/40 rounded-lg border px-3 py-1 text-sm disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            Previous
                        </button>
                        <button
                            wire:click="nextPage"
                            @disabled(! $this->activities->hasMorePages())
                            class="border-brand-700/60 text-brand-200 hover:bg-brand-300/5 hover:border-brand-300/40 rounded-lg border px-3 py-1 text-sm disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            Next
                        </button>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-filament-panels::page>
