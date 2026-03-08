<x-filament-panels::page>
    <div style="max-width: 900px; margin: 0 auto;">
        {{-- Storefront URL Banner --}}
        <div style="background: linear-gradient(135deg, #4E342E, #6D4C41); color: #FFF8E1; padding: 1.5rem 2rem; border-radius: 12px; margin-bottom: 1.5rem; text-align: center;">
            <p style="margin: 0 0 0.25rem; font-size: 0.875rem; opacity: 0.8;">Your Storefront URL</p>
            <p style="margin: 0; font-size: 1.25rem; font-weight: 700; word-break: break-all;">
                <a href="{{ $this->currentUrl }}" target="_blank" style="color: #FFE0B2; text-decoration: underline;">
                    {{ $this->currentUrl }}
                </a>
            </p>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; align-items: start;">
            {{-- Options --}}
            <div style="background: #FFF8E1; border: 1px solid #D7CCC8; border-radius: 12px; padding: 1.5rem;">
                <h3 style="margin: 0 0 1rem; color: #3E2723; font-size: 1.1rem; font-weight: 600;">⚙️ QR Code Options</h3>
                {{ $this->form }}

                <div style="margin-top: 1.25rem;">
                    <button
                        wire:click="downloadQrCode"
                        style="width: 100%; padding: 0.75rem 1.5rem; background: #5D4037; color: #FFF8E1; border: none; border-radius: 8px; font-size: 1rem; font-weight: 600; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 0.5rem;"
                        onmouseover="this.style.background='#4E342E'"
                        onmouseout="this.style.background='#5D4037'"
                    >
                        ⬇️ Download QR Code
                    </button>
                </div>
            </div>

            {{-- Preview --}}
            <div style="background: #FFFFFF; border: 1px solid #D7CCC8; border-radius: 12px; padding: 1.5rem; text-align: center;">
                <h3 style="margin: 0 0 1rem; color: #3E2723; font-size: 1.1rem; font-weight: 600;">👁️ Preview</h3>
                <div style="background: white; padding: 1.5rem; border-radius: 8px; display: inline-block; border: 2px dashed #BCAAA4;">
                    @if(($this->data['format'] ?? 'svg') === 'png')
                        <img src="data:image/png;base64,{{ $this->qrCodeSvg }}" alt="QR Code" style="max-width: 100%;">
                    @else
                        {!! $this->qrCodeSvg !!}
                    @endif
                </div>
                <p style="margin: 1rem 0 0; color: #6D4C41; font-size: 0.8rem;">
                    {{ ['', 'Home', 'menu' => 'Menu', 'order' => 'Order Page', 'contact' => 'Contact'][$this->data['page'] ?? ''] ?? 'Home' }}
                    · {{ $this->data['size'] ?? 300 }}px
                    · {{ strtoupper($this->data['format'] ?? 'svg') }}
                </p>
            </div>
        </div>
    </div>
</x-filament-panels::page>
