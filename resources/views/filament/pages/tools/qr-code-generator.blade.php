<x-filament-panels::page>
    <div class="mx-auto max-w-[900px]">
        {{-- Storefront URL Banner --}}
        <div class="mb-6 rounded-xl bg-gradient-to-br from-[#4E342E] to-[#6D4C41] px-8 py-6 text-center text-[#FFF8E1]">
            <p class="m-0 mb-1 text-sm opacity-80">Your Storefront URL</p>
            <p class="m-0 text-[1.25rem] font-bold break-all">
                <a href="{{ $this->currentUrl }}" target="_blank" class="text-[#FFE0B2] underline">
                    {{ $this->currentUrl }}
                </a>
            </p>
        </div>

        <div class="grid grid-cols-2 items-start gap-6">
            {{-- Options --}}
            <div class="rounded-xl border border-[#D7CCC8] bg-[#FFF8E1] p-6">
                <h3 class="m-0 mb-4 flex items-center gap-2 text-[1.1rem] font-semibold text-[#3E2723]">
                    <x-filament::icon icon="heroicon-o-cog-6-tooth" class="h-5 w-5" />
                    QR Code Options
                </h3>
                {{ $this->form }}

                <div class="mt-5">
                    <button
                        wire:click="downloadQrCode"
                        class="flex w-full cursor-pointer items-center justify-center gap-2 rounded-lg border-0 bg-[#5D4037] px-6 py-3 text-base font-semibold text-[#FFF8E1] transition-colors hover:bg-[#4E342E]"
                    >
                        <x-filament::icon icon="heroicon-o-arrow-down-tray" class="h-5 w-5" />
                        Download QR Code
                    </button>
                </div>
            </div>

            {{-- Preview --}}
            <div class="rounded-xl border border-[#D7CCC8] bg-white p-6 text-center">
                <h3 class="m-0 mb-4 flex items-center justify-center gap-2 text-[1.1rem] font-semibold text-[#3E2723]">
                    <x-filament::icon icon="heroicon-o-eye" class="h-5 w-5" />
                    Preview
                </h3>
                <div class="inline-block rounded-lg border-2 border-dashed border-[#BCAAA4] bg-white p-6">
                    @if (($this->data['format'] ?? 'svg') === 'png')
                        <img src="data:image/png;base64,{{ $this->qrCodeSvg }}" alt="QR Code" class="max-w-full" />
                    @else
                        {!! $this->qrCodeSvg !!}
                    @endif
                </div>
                <p class="m-0 mt-4 text-[0.8rem] text-[#6D4C41]">
                    {{ ['', 'Home', 'menu' => 'Menu', 'order' => 'Order Page', 'contact' => 'Contact'][$this->data['page'] ?? ''] ?? 'Home' }} · {{ $this->data['size'] ?? 300 }}px
                    · {{ strtoupper($this->data['format'] ?? 'svg') }}
                </p>
            </div>
        </div>
    </div>
</x-filament-panels::page>
