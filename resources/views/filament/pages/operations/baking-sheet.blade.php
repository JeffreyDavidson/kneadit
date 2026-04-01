<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Date Picker -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-900">Baking Sheet</h2>
                <div class="flex space-x-2">
                    <input type="date"
                           wire:model.live="selectedDate"
                           class="rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500" />
                </div>
            </div>
        </div>

        <!-- Baking Items List -->
        <div class="bg-white rounded-lg shadow print:shadow-none print:rounded-none" id="baking-sheet">
            <div class="p-6 print:p-4">
                <div class="flex items-center justify-between mb-6 print:mb-4">
                    <h3 class="text-xl font-bold text-gray-900">
                        Baking Sheet - {{ \Carbon\Carbon::parse($selectedDate)->format('F j, Y') }}
                    </h3>
                    <div class="text-sm text-gray-500 print:hidden">
                        Total Items: {{ $bakingItems->count() }}
                    </div>
                </div>

                @if ($bakingItems->isEmpty())
                    <div class="text-center py-8 text-gray-500">
                        <svg class="w-12 h-12 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                        <p class="text-lg">No orders for this date</p>
                        <p class="text-sm">Try selecting a different date</p>
                    </div>
                @else
                    <div class="space-y-4">
                        @foreach ($bakingItems as $item)
                            <div class="border border-gray-200 rounded-lg p-4 print:border-gray-400">
                                <div class="flex justify-between items-start mb-2">
                                    <h4 class="font-semibold text-lg text-gray-900">{{ $item->product_name }}</h4>
                                    <span class="bg-primary-100 text-primary-800 px-3 py-1 rounded-full text-sm font-medium">
                                        {{ $item->total_quantity }} {{ $item->total_quantity == 1 ? 'unit' : 'units' }}
                                    </span>
                                </div>

                                <div class="text-gray-600">
                                    <p class="text-sm">
                                        <strong>Customers:</strong>
                                        {{ $item->customer_names }}
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Print Styles -->
    <style>
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

            #baking-sheet {
                page-break-inside: avoid;
            }

            .space-y-4 > * + * {
                page-break-inside: avoid;
            }
        }
    </style>

    <!-- Print JavaScript -->
    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('print-page', () => {
                window.print();
            });
        });
    </script>
</x-filament-panels::page>