<x-filament-panels::page>
    @php
        $categories = $this->getCategories();
        $store = $this->getStoreInfo();
        $url = $this->getStorefrontUrl();
        $qr = $this->getQrCode();
        $isElegant = $this->menuLayout === 'elegant';
    @endphp

    {{-- Controls (hidden when printing) --}}
    <div class="no-print mb-6 space-y-4">
        <div class="flex flex-wrap items-center gap-3">
            <button
                wire:click="setView('menu')"
                class="px-4 py-2 rounded-lg text-sm font-medium transition {{ $this->activeView === 'menu' ? 'bg-primary-600 text-white' : 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300' }}"
            >
                Full Menu
            </button>
            <button
                wire:click="setView('pricelist')"
                class="px-4 py-2 rounded-lg text-sm font-medium transition {{ $this->activeView === 'pricelist' ? 'bg-primary-600 text-white' : 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300' }}"
            >
                Price List
            </button>

            <div class="mx-2 h-8 border-l border-gray-300 dark:border-gray-600"></div>

            <button
                wire:click="setLayout('elegant')"
                class="px-4 py-2 rounded-lg text-sm font-medium transition {{ $isElegant ? 'bg-primary-600 text-white' : 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300' }}"
            >
                Elegant
            </button>
            <button
                wire:click="setLayout('modern')"
                class="px-4 py-2 rounded-lg text-sm font-medium transition {{ !$isElegant ? 'bg-primary-600 text-white' : 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300' }}"
            >
                Modern
            </button>

            <div class="ml-auto flex gap-3">
                <button
                    onclick="window.print()"
                    class="bg-primary-600 hover:bg-primary-700 flex items-center gap-2 rounded-lg px-5 py-2 text-sm font-medium text-white transition"
                >
                    <x-heroicon-s-printer class="h-4 w-4" />
                    Print Menu
                </button>
                <button
                    onclick="window.print()"
                    class="flex items-center gap-2 rounded-lg bg-gray-600 px-5 py-2 text-sm font-medium text-white transition hover:bg-gray-700"
                    title="Use your browser's 'Save as PDF' option in the print dialog"
                >
                    <x-heroicon-s-arrow-down-tray class="h-4 w-4" />
                    Download as PDF
                </button>
            </div>
        </div>
    </div>

    {{-- Printable Content --}}
    <div
        id="printable-content"
        style="font-family: {{ $isElegant ? "'Playfair Display', 'Georgia', serif" : "'Inter', 'Helvetica Neue', sans-serif" }}; color: #1a1a1a; max-width: 800px; margin: 0 auto; background: white; padding: 40px;"
    >
        {{-- Header --}}
        <div style="text-align: center; margin-bottom: 32px; border-bottom: {{ $isElegant ? '2px solid #3E2723' : '4px solid #D97706' }}; padding-bottom: 24px;">
            <h1
                style="font-size: 36px; font-weight: 700; margin: 0 0 8px 0; color: {{ $isElegant ? '#3E2723' : '#D97706' }}; font-family: {{ $isElegant ? "'Playfair Display', Georgia, serif" : "'Inter', sans-serif" }}; letter-spacing: {{ $isElegant ? '2px' : '0' }};"
            >
                {{ $store['name'] }}
            </h1>
            @if ($store['tagline'])
                <p style="font-size: 16px; color: #666; margin: 0 0 8px 0; font-style: {{ $isElegant ? 'italic' : 'normal' }};">
                    {{ $store['tagline'] }}
                </p>
            @endif
            @if ($store['phone'] || $store['email'])
                <p style="font-size: 13px; color: #888; margin: 0">
                    {{ collect([$store['phone'], $store['email'], $store['address']])->filter()->implode(' · ') }}
                </p>
            @endif
        </div>

        @if ($this->activeView === 'menu')
            {{-- Full Menu View --}}
            @foreach ($categories as $category)
                <div style="margin-bottom: 32px; {{ !$loop->first ? 'page-break-before: auto;' : '' }}">
                    <h2
                        style="font-size: 24px; font-weight: 600; margin: 0 0 16px 0; color: {{ $isElegant ? '#3E2723' : '#D97706' }}; border-bottom: 1px solid #e5e5e5; padding-bottom: 8px; font-family: {{ $isElegant ? "'Playfair Display', Georgia, serif" : "'Inter', sans-serif" }};"
                    >
                        {{ $category->name }}
                    </h2>
                    @foreach ($category->products as $product)
                        <div style="display: flex; align-items: flex-start; gap: 16px; margin-bottom: 16px; padding-bottom: 16px; {{ !$loop->last ? 'border-bottom: 1px dotted #e5e5e5;' : '' }}">
                            @if ($product->image)
                                <img
                                    src="{{ asset('storage/' . $product->image) }}"
                                    alt="{{ $product->name }}"
                                    style="width: 80px; height: 80px; object-fit: cover; border-radius: {{ $isElegant ? '4px' : '12px' }}; flex-shrink: 0;"
                                />
                            @endif
                            <div style="flex: 1; min-width: 0">
                                <div
                                    style="
                                        display: flex;
                                        justify-content: space-between;
                                        align-items: baseline;
                                        gap: 8px;
                                    "
                                >
                                    <h3
                                        style="font-size: 17px; font-weight: 600; margin: 0; font-family: {{ $isElegant ? "'Playfair Display', Georgia, serif" : "'Inter', sans-serif" }};"
                                    >
                                        {{ $product->name }}
                                    </h3>
                                    <span style="font-size: 17px; font-weight: 700; white-space: nowrap; color: {{ $isElegant ? '#3E2723' : '#D97706' }};">
                                        @money($product->price)
                                    </span>
                                </div>
                                @if ($product->description)
                                    <p style="font-size: 13px; color: #666; margin: 4px 0 0 0; line-height: 1.4">
                                        {{ $product->description }}
                                    </p>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endforeach
        @else
            {{-- Price List View --}}
            <div style="columns: 2; column-gap: 40px">
                @foreach ($categories as $category)
                    <div style="break-inside: avoid; margin-bottom: 24px">
                        <h2 style="font-size: 18px; font-weight: 700; margin: 0 0 8px 0; color: {{ $isElegant ? '#3E2723' : '#D97706' }}; text-transform: uppercase; letter-spacing: 1px; font-size: 14px; border-bottom: 1px solid #ccc; padding-bottom: 4px;">
                            {{ $category->name }}
                        </h2>
                        @foreach ($category->products as $product)
                            <div
                                style="
                                    display: flex;
                                    justify-content: space-between;
                                    align-items: baseline;
                                    padding: 3px 0;
                                    font-size: 14px;
                                "
                            >
                                <span
                                    style="
                                        overflow: hidden;
                                        text-overflow: ellipsis;
                                        white-space: nowrap;
                                        margin-right: 8px;
                                    "
                                >{{ $product->name }}</span>
                                <span style="white-space: nowrap; font-weight: 600">@money($product->price)</span>
                            </div>
                        @endforeach
                    </div>
                @endforeach
            </div>
        @endif

        {{-- Allergy Disclaimer --}}
        @if ($store['disclaimer'])
            <div
                style="
                    margin-top: 24px;
                    padding: 12px 16px;
                    background: #fefce8;
                    border: 1px solid #fde68a;
                    border-radius: 6px;
                    font-size: 12px;
                    color: #92400e;
                "
            >
                {{ $store['disclaimer'] }}
            </div>
        @endif

        {{-- Business Card Section --}}
        <div style="margin-top: 40px; padding-top: 24px; border-top: 2px solid {{ $isElegant ? '#3E2723' : '#D97706' }}; display: flex; justify-content: space-between; align-items: center;">
            <div style="font-size: 13px; line-height: 1.6">
                <strong style="font-size: 16px">{{ $store['name'] }}</strong><br />
                @if ($store['phone'])
                    <span>Phone: {{ $store['phone'] }}</span
                    ><br />
                @endif
                @if ($store['email'])
                    <span>Email: {{ $store['email'] }}</span
                    ><br />
                @endif
                @if ($store['address'])
                    <span>Address: {{ $store['address'] }}</span
                    ><br />
                @endif
                <span style="color: #666">{{ $url }}</span>
            </div>
            <div style="text-align: center">
                <div>{!! $qr !!}</div>
                <p style="font-size: 10px; color: #888; margin: 4px 0 0 0">Scan to order online</p>
            </div>
        </div>
    </div>

    {{-- Google Fonts --}}
    <link
        href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Inter:wght@400;500;600;700&display=swap"
        rel="stylesheet"
    />

    {{-- Print Styles --}}
    <style @cspnonce>
        @media print {
            /* Hide Filament chrome */
            .fi-sidebar,
            .fi-topbar,
            .fi-header,
            .fi-breadcrumbs,
            .fi-page-header,
            .no-print,
            nav,
            header,
            aside,
            footer,
            [class*='fi-sidebar'],
            [class*='fi-topbar'] {
                display: none !important;
            }
            body,
            .fi-body,
            .fi-main,
            .fi-main-ctn,
            [class*='fi-page'] {
                background: white !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            #printable-content {
                padding: 20px !important;
                max-width: 100% !important;
                box-shadow: none !important;
            }
            @page {
                margin: 0.75in;
            }
        }

        /* Screen preview styling */
        @media screen {
            #printable-content {
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
                border-radius: 8px;
                border: 1px solid #e5e7eb;
            }
        }
    </style>
</x-filament-panels::page>
