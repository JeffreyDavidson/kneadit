<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Date Range Picker -->
        <div class="rounded-lg bg-white p-6 shadow">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-900">Shopping List Generator</h2>
            </div>

            <div class="mb-4 grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                    <label for="start_date" class="mb-1 block text-sm font-medium text-gray-700">Start Date</label>
                    <input
                        type="date"
                        id="start_date"
                        wire:model="startDate"
                        class="focus:border-primary-500 focus:ring-primary-500 w-full rounded-md border-gray-300 shadow-sm"
                    />
                </div>
                <div>
                    <label for="end_date" class="mb-1 block text-sm font-medium text-gray-700">End Date</label>
                    <input
                        type="date"
                        id="end_date"
                        wire:model="endDate"
                        class="focus:border-primary-500 focus:ring-primary-500 w-full rounded-md border-gray-300 shadow-sm"
                    />
                </div>
            </div>

            <div class="text-sm text-gray-600">
                Generate a shopping list for ingredients needed for orders between these dates.
            </div>
        </div>

        <!-- Shopping List -->
        @if ($shoppingList->isNotEmpty())
            <div class="rounded-lg bg-white shadow print:rounded-none print:shadow-none" id="shopping-list">
                <div class="p-6 print:p-4">
                    <div class="mb-6 flex items-center justify-between print:mb-4">
                        <h3 class="text-xl font-bold text-gray-900">Shopping List</h3>
                        <div class="text-sm text-gray-500 print:hidden">
                            {{ \Carbon\Carbon::parse($startDate)->format('M j') }} - {{ \Carbon\Carbon::parse($endDate)->format('M j, Y') }}
                        </div>
                    </div>

                    <div class="space-y-2">
                        @foreach ($shoppingList as $index => $ingredient)
                            <div class="flex items-center space-x-3 rounded-md p-2 hover:bg-gray-50 print:hover:bg-transparent">
                                <div class="flex-shrink-0">
                                    <input
                                        type="checkbox"
                                        wire:click="toggleItem({{ $index }})"
                                        @checked(isset($checkedItems[$index]))
                                        class="text-primary-600 focus:ring-primary-500 h-4 w-4 rounded border-gray-300"
                                    />
                                </div>
                                <div class="flex-1 {{ isset($checkedItems[$index]) ? 'line-through text-gray-500' : '' }}">
                                    <span class="font-medium">{{ $ingredient['name'] }}</span>
                                    <span class="ml-2 text-gray-600">
                                        {{ number_format($ingredient['quantity'], 2) }}
                                        @if ($ingredient['unit'])
                                            {{ $ingredient['unit'] }}
                                        @endif
                                    </span>
                                    @if (isset($ingredient['in_stock']) && $ingredient['in_stock'] !== null)
                                        @if (! $ingredient['needs_purchase'])
                                            <span class="ml-2 inline-flex items-center rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-800">
                                                <span class="inline-flex items-center gap-1">
                                                    <x-filament::icon icon="heroicon-o-check-circle" class="h-4 w-4" />
                                                    In stock ({{ number_format($ingredient['in_stock'], 1) }} {{ $ingredient['stock_unit'] }})
                                                </span>
                                            </span>
                                        @else
                                            <span class="ml-2 inline-flex items-center rounded-full bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-800">
                                                Need {{ number_format($ingredient['deficit'], 1) }} more ({{ number_format($ingredient['in_stock'], 1) }} in
                                                stock)
                                            </span>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>

                    @if ($shoppingList->count() > 0)
                        <div class="mt-6 border-t border-gray-200 pt-4 print:border-gray-400">
                            <div class="flex justify-between text-sm text-gray-600">
                                <span>Total items: {{ $shoppingList->count() }}</span>
                                <span class="print:hidden">
                                    Checked: {{ count($checkedItems) }} / {{ $shoppingList->count() }}
                                </span>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @elseif ($startDate && $endDate)
            <div class="rounded-lg bg-white p-6 shadow">
                <div class="py-8 text-center text-gray-500">
                    <svg class="mx-auto mb-4 h-12 w-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                    <p class="text-lg">No ingredients needed</p>
                    <p class="text-sm">No orders with recipes found for the selected date range</p>
                </div>
            </div>
        @endif
    </div>

    <!-- Print Styles -->
    <style @cspnonce>
        @media print {
            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .print\\:hidden {
                display: none !important;
            }

            .print\\:shadow-none {
                box-shadow: none !important;
            }

            .print\\:rounded-none {
                border-radius: 0 !important;
            }

            .print\\:p-4 {
                padding: 1rem !important;
            }

            .print\\:mb-4 {
                margin-bottom: 1rem !important;
            }

            .print\\:border-gray-400 {
                border-color: #9ca3af !important;
            }

            .print\\:hover\\:bg-transparent:hover {
                background-color: transparent !important;
            }

            #shopping-list {
                page-break-inside: avoid;
            }

            .space-y-2 > * + * {
                page-break-inside: avoid;
            }
        }
    </style>

    <!-- Print JavaScript -->
    <script @cspnonce>
        document.addEventListener('livewire:init', () => {
            Livewire.on('print-shopping-list', () => {
                window.print();
            });
        });
    </script>
</x-filament-panels::page>
