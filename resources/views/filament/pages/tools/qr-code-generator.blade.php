<x-filament-panels::page>
    <div class="max-w-[900px] mx-auto">
        {{-- Storefront URL Banner --}}
        <div class="bg-gradient-to-br from-[#4E342E] to-[#6D4C41] text-[#FFF8E1] px-8 py-6 rounded-xl mb-6 text-center">
            <p class="m-0 mb-1 text-sm opacity-80">Your Storefront URL</p>
            <p class="m-0 text-[1.25rem] font-bold break-all">
                <a href="{{ $this->currentUrl }}" target="_blank" class="text-[#FFE0B2] underline">
                    {{ $this->currentUrl }}
                </a>
            </p>
        </div>

        <div class="grid grid-cols-2 gap-6 items-start">
            {{-- Options --}}
            <div class="bg-[#FFF8E1] border border-[#D7CCC8] rounded-xl p-6">
                <h3 class="m-0 mb-4 text-[#3E2723] text-[1.1rem] font-semibold">⚙️ QR Code Options</h3>
                {{ $this->form }}

                <div class="mt-5">
                    <button wire:click="downloadQrCode"
                        class="w-full px-6 py-3 bg-[#5D4037] hover:bg-[#4E342E] text-[#FFF8E1] border-0 rounded-lg text-base font-semibold cursor-pointer flex items-center justify-center gap-2 transition-colors">
                        ⬇️ Download QR Code
                    </button>
                </div>
            </div>

            {{-- Preview --}}
            <div class="bg-white border border-[#D7CCC8] rounded-xl p-6 text-center">
                <h3 class="m-0 mb-4 text-[#3E2723] text-[1.1rem] font-semibold">👁️ Preview</h3>
                <div class="bg-white p-6 rounded-lg inline-block border-2 border-dashed border-[#BCAAA4]">
                    @if (($this->data['format'] ?? 'svg') === 'png')
                        <img src="data:image/png;base64,{{ $this->qrCodeSvg }}" alt="QR Code" class="max-w-full">
                    @else
                        {!! $this->qrCodeSvg !!}
                    @endif
                </div>
                <p class="mt-4 m-0 text-[#6D4C41] text-[0.8rem]">
                    {{ ['', 'Home', 'menu' => 'Menu', 'order' => 'Order Page', 'contact' => 'Contact'][$this->data['page'] ?? ''] ?? 'Home' }}
                    · {{ $this->data['size'] ?? 300 }}px
                    · {{ strtoupper($this->data['format'] ?? 'svg') }}
                </p>
            </div>
        </div>
    </div>
</x-filament-panels::page>
