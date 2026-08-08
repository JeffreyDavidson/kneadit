<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Form sections (Export & Import) --}}
        {{ $this->form }}

        {{-- Preview Section --}}
        @if ($previewErrors && count($previewErrors) > 0)
            <div class="rounded-xl border border-red-200 bg-red-50 p-6 dark:border-red-800 dark:bg-red-900/20">
                <h3 class="mb-3 flex items-center gap-2 text-lg font-semibold text-red-800 dark:text-red-200">
                    <x-filament::icon icon="heroicon-o-exclamation-triangle" class="h-5 w-5" />
                    Validation Errors
                </h3>
                <ul class="list-inside list-disc space-y-1 text-sm text-red-700 dark:text-red-300">
                    @foreach ($previewErrors as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if ($previewData && count($previewData) > 0)
            <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <h3 class="mb-4 flex items-center gap-2 text-lg font-semibold text-gray-900 dark:text-gray-100">
                    <x-filament::icon icon="heroicon-o-clipboard-document-list" class="h-5 w-5" />
                    Preview ({{ count($previewData) }} rows)
                </h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-gray-50 text-xs text-gray-600 uppercase dark:bg-gray-700 dark:text-gray-300">
                            <tr>
                                <th class="px-4 py-2">Row</th>
                                <th class="px-4 py-2">Name</th>
                                <th class="px-4 py-2">Category</th>
                                <th class="px-4 py-2">Price</th>
                                <th class="px-4 py-2">Cost</th>
                                <th class="px-4 py-2">Active</th>
                                <th class="px-4 py-2">Featured</th>
                                <th class="px-4 py-2">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach ($previewData as $row)
                                <tr class="{{ !empty($row['_errors']) ? 'bg-red-50 dark:bg-red-900/10' : '' }}">
                                    <td class="px-4 py-2 text-gray-500">{{ $row['_line'] }}</td>
                                    <td class="px-4 py-2 font-medium text-gray-900 dark:text-gray-100">
                                        {{ $row['name'] ?? '' }}
                                    </td>
                                    <td class="px-4 py-2 text-gray-600 dark:text-gray-400">
                                        {{ $row['category'] ?? '' }}
                                    </td>
                                    <td class="px-4 py-2 text-gray-600 dark:text-gray-400">
                                        {{ $row['price'] ?? '' }}
                                    </td>
                                    <td class="px-4 py-2 text-gray-600 dark:text-gray-400">{{ $row['cost'] ?? '' }}</td>
                                    <td class="px-4 py-2 text-gray-600 dark:text-gray-400">
                                        @if ($row['is_active'] ?? '1')
                                            <x-filament::icon
                                                icon="heroicon-o-check-circle"
                                                class="h-5 w-5 text-green-600"
                                            />
                                        @else
                                            <x-filament::icon icon="heroicon-o-x-circle" class="h-5 w-5 text-red-600" />
                                        @endif
                                    </td>
                                    <td class="px-4 py-2 text-gray-600 dark:text-gray-400">
                                        @if ($row['is_featured'] ?? '0')
                                            <x-filament::icon icon="heroicon-o-star" class="h-5 w-5 text-amber-500" />
                                        @endif
                                    </td>
                                    <td class="px-4 py-2">
                                        @if (! empty($row['_errors']))
                                            <span class="text-xs text-red-600">{{ implode(', ', $row['_errors']) }}</span>
                                        @else
                                            <span class="text-xs text-green-600">OK</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        {{-- Import Results --}}
        @if ($importResults)
            <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <h3 class="mb-4 flex items-center gap-2 text-lg font-semibold text-gray-900 dark:text-gray-100">
                    <x-filament::icon icon="heroicon-o-chart-bar-square" class="h-5 w-5" />
                    Import Results
                </h3>
                <div class="mb-4 grid grid-cols-3 gap-4">
                    <div class="rounded-lg bg-green-50 p-4 text-center dark:bg-green-900/20">
                        <div class="text-2xl font-bold text-green-700 dark:text-green-300">
                            {{ $importResults['created'] }}
                        </div>
                        <div class="text-sm text-green-600 dark:text-green-400">Created</div>
                    </div>
                    <div class="rounded-lg bg-blue-50 p-4 text-center dark:bg-blue-900/20">
                        <div class="text-2xl font-bold text-blue-700 dark:text-blue-300">
                            {{ $importResults['updated'] }}
                        </div>
                        <div class="text-sm text-blue-600 dark:text-blue-400">Updated</div>
                    </div>
                    <div class="rounded-lg bg-red-50 p-4 text-center dark:bg-red-900/20">
                        <div class="text-2xl font-bold text-red-700 dark:text-red-300">
                            {{ count($importResults['errors']) }}
                        </div>
                        <div class="text-sm text-red-600 dark:text-red-400">Errors</div>
                    </div>
                </div>
                @if (count($importResults['errors']) > 0)
                    <div class="mt-4">
                        <h4 class="mb-2 font-medium text-red-700 dark:text-red-300">Errors:</h4>
                        <ul class="list-inside list-disc space-y-1 text-sm text-red-600 dark:text-red-400">
                            @foreach ($importResults['errors'] as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        @endif
    </div>
</x-filament-panels::page>
