<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Filament-rendered date picker (Section + Flatpickr-themed DatePicker) --}}
        {{ $this->content }}

        <!-- Baking Items List -->
        <div
            class="bg-brand-800 border-brand-700/60 rounded-xl border print:rounded-none print:border-gray-300 print:bg-white"
            id="baking-sheet"
        >
            <div class="p-6 print:p-4">
                <div class="mb-6 flex items-center justify-between print:mb-4">
                    <h3 class="text-xl font-bold text-white print:text-gray-900">
                        Baking Sheet - {{ \Carbon\Carbon::parse($selectedDate)->format('F j, Y') }}
                    </h3>
                    <div class="text-brand-400 text-sm print:hidden print:text-gray-500">
                        Total Items: {{ $this->bakingItems->count() }}
                    </div>
                </div>

                @if ($this->bakingItems->isEmpty())
                    <div class="text-brand-400 py-8 text-center">
                        <svg class="mx-auto mb-4 h-12 w-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                        <p class="text-lg">No orders for this date</p>
                        <p class="text-sm">Try selecting a different date</p>
                    </div>
                @else
                    <div class="space-y-4">
                        @foreach ($this->bakingItems as $item)
                            <div class="border-brand-700/60 rounded-lg border p-4 print:border-gray-400">
                                <div class="mb-2 flex items-start justify-between">
                                    <h4 class="text-lg font-semibold text-white print:text-gray-900">
                                        {{ $item->product_name }}
                                    </h4>
                                    <span class="bg-brand-300/15 text-brand-300 border-brand-300/30 rounded-full border px-3 py-1 text-sm font-medium print:border-gray-300 print:bg-gray-100 print:text-gray-800">
                                        {{ $item->total_quantity }} {{ $item->total_quantity == 1 ? 'unit' : 'units' }}
                                    </span>
                                </div>

                                <div class="text-brand-200 print:text-gray-600">
                                    <p class="text-sm">
                                        <strong class="text-brand-100 print:text-gray-900">Customers:</strong>
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
    <style @cspnonce>
        @media print {
            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            /* Strip dashboard chrome from print flow — without this, the
               sidebar + topbar + page heading + date picker section all
               keep their height and the baking sheet ends up on page 2.
               :has() walks down the ancestor chain to #baking-sheet and
               display:none everything outside that chain so html/body
               collapse to just the printed content. */
            html,
            body {
                height: auto !important;
                min-height: 0 !important;
                margin: 0 !important;
                padding: 0 !important;
                background: white !important;
            }

            body > *:not(:has(#baking-sheet)) {
                display: none !important;
            }

            body :has(#baking-sheet) > *:not(:has(#baking-sheet)):not(#baking-sheet) {
                display: none !important;
            }

            .print\\:hidden {
                display: none !important;
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
    <script @cspnonce>
        document.addEventListener('livewire:init', () => {
            Livewire.on('print-page', () => {
                window.print();
            });
        });
    </script>
</x-filament-panels::page>
