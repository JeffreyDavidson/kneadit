<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Filament-rendered form (Section + Selects + Grid + Checkboxes) --}}
        {{ $this->content }}

        {{-- Action row --}}
        <div class="flex gap-3">
            <x-filament::button wire:click="generateLabels" icon="heroicon-o-eye">
                Generate Preview
            </x-filament::button>
            @if ($showPreview)
                <x-filament::button color="success" icon="heroicon-o-printer" onclick="window.print()">
                    Print Labels
                </x-filament::button>
            @endif
        </div>

        {{-- Label Preview --}}
        @if ($showPreview && ! empty($selectedProducts))
            @php
                $products = $this->getSelectedProductModels();
                $dims = $this->getLabelDimensions();
                $storeName = $this->getStoreName();
                $allergyDisclaimer = $this->getAllergyDisclaimer();
            @endphp

            <div class="print-area" id="label-print-area">
                <div
                    class="label-grid"
                    style="display: grid; grid-template-columns: repeat({{ $dims['cols'] }}, 1fr); gap: 4px; padding: 0.25in;"
                >
                    @foreach ($products as $product)
                        @for ($i = 0; $i < $quantity; $i++)
                            <div
                                class="label-card"
                                style="width: {{ $dims['width'] }}; height: {{ $dims['height'] }}; border: 1px solid #ccc; border-radius: 4px; padding: 6px; overflow: hidden; display: flex; flex-direction: column; justify-content: space-between; font-family: Arial, sans-serif; page-break-inside: avoid; background: white;"
                            >
                                {{-- Header --}}
                                <div
                                    style="
                                        text-align: center;
                                        border-bottom: 1px solid #eee;
                                        padding-bottom: 3px;
                                        margin-bottom: 3px;
                                    "
                                >
                                    <div
                                        style="
                                            font-size: 7px;
                                            text-transform: uppercase;
                                            letter-spacing: 1px;
                                            color: #8b4513;
                                            font-weight: bold;
                                        "
                                    >
                                        {{ $storeName }}
                                    </div>
                                </div>

                                {{-- Product Name & Price --}}
                                <div
                                    style="
                                        text-align: center;
                                        flex: 1;
                                        display: flex;
                                        flex-direction: column;
                                        justify-content: center;
                                    "
                                >
                                    <div style="font-size: {{ $labelSize === 'small' ? '9px' : '12px' }}; font-weight: bold; color: #333; line-height: 1.2;">
                                        {{ $product->name }}
                                    </div>
                                    <div style="font-size: {{ $labelSize === 'small' ? '10px' : '14px' }}; font-weight: bold; color: #8b4513; margin-top: 2px;">
                                        @money($product->price)
                                    </div>
                                </div>

                                @if ($labelSize !== 'small')
                                    {{-- Ingredients --}}
                                    @if ($product->recipe && $product->recipe->ingredients)
                                        <div
                                            style="
                                                font-size: 6px;
                                                color: #666;
                                                text-align: center;
                                                line-height: 1.3;
                                                margin: 2px 0;
                                            "
                                        >
                                            <strong>Ingredients:</strong>
                                            {{ Str::limit(is_array($product->recipe->ingredients) ? implode(', ', $product->recipe->ingredients) : $product->recipe->ingredients, $labelSize === 'medium' ? 80 : 150) }}
                                        </div>
                                    @endif

                                    {{-- Allergy Disclaimer --}}
                                    @if ($includeAllergyDisclaimer)
                                        <div
                                            style="
                                                font-size: 5px;
                                                color: #999;
                                                text-align: center;
                                                font-style: italic;
                                                margin: 1px 0;
                                            "
                                        >
                                            {{ Str::limit($allergyDisclaimer, $labelSize === 'medium' ? 100 : 200) }}
                                        </div>
                                    @endif
                                @endif

                                {{-- Barcode --}}
                                @if ($includeBarcode)
                                    <div style="text-align: center; margin: 2px 0">
                                        <div
                                            class="css-barcode"
                                            style="display: inline-flex; gap: 1px; height: {{ $labelSize === 'small' ? '12px' : '18px' }};"
                                        >
                                            @php
                                                $code = str_pad($product->id, 8, '0', STR_PAD_LEFT);
                                                $bars = str_split(hash('xxh128', $code));
                                            @endphp
                                            @foreach (array_slice($bars, 0, 20) as $bar)
                                                <div style="width: {{ hexdec($bar) % 2 === 0 ? '1px' : '2px' }}; height: 100%; background: {{ hexdec($bar) % 3 === 0 ? 'white' : 'black' }};"></div>
                                            @endforeach
                                        </div>
                                        <div style="font-size: 5px; color: #666; font-family: monospace">
                                            {{ $code }}
                                        </div>
                                    </div>
                                @endif

                                {{-- QR Code --}}
                                @if ($includeQrCode)
                                    <div style="text-align: center; margin: 2px 0">
                                        {!! QrCode::size($labelSize === 'small' ? 30 : 50)->generate(url('/menu#product-'.$product->id)) !!}
                                    </div>
                                @endif

                                {{-- Best By --}}
                                <div
                                    style="
                                        text-align: center;
                                        font-size: 6px;
                                        color: #888;
                                        border-top: 1px solid #eee;
                                        padding-top: 2px;
                                        margin-top: auto;
                                    "
                                >
                                    Best by: {{ \Carbon\Carbon::parse($bestByDate)->format('M j, Y') }}
                                </div>
                            </div>
                        @endfor
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    @pushOnce('styles')
        <style @cspnonce>
            @media print {
                /* Why labels were spilling to a 2nd page: `visibility: hidden`
                   keeps elements in layout, so the dashboard's sidebar +
                   topbar + page heading + form all still reserved their
                   vertical space. Body height = full dashboard layout,
                   pushing labels past the first printed page even though
                   they were the only visible content.

                   Fix: hide everything OUTSIDE the .print-area's ancestor
                   chain via `:not(:has(...))` so those branches collapse
                   (display:none), and let the .print-area chain stay in
                   normal flow with its real heights intact. */
                html,
                body {
                    height: auto !important;
                    min-height: 0 !important;
                    margin: 0 !important;
                    padding: 0 !important;
                    background: white !important;
                }

                /* Hide every body subtree that doesn't contain .print-area. */
                body > *:not(:has(.print-area)) {
                    display: none !important;
                }

                /* Within the chain that DOES contain .print-area, hide
                   sibling subtrees that aren't an ancestor of it. */
                body :has(.print-area) > *:not(:has(.print-area)):not(.print-area) {
                    display: none !important;
                }

                .print-area {
                    position: static !important;
                    width: 100% !important;
                }

                .label-card {
                    box-shadow: none !important;
                    -webkit-print-color-adjust: exact !important;
                    print-color-adjust: exact !important;
                }

                /* Page setup for Avery 5160 */
                @page {
                    margin: 0.5in 0.19in;
                    size: letter;
                }
            }

            /* Screen preview styling */
            .label-card {
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
                transition: box-shadow 0.2s;
            }
            .label-card:hover {
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
            }
        </style>
    @endPushOnce
</x-filament-panels::page>
