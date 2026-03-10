<x-filament-panels::page>
    <div style="max-width: 700px; margin: 0 auto;">
        <div style="background: #1c1410; border: 1px solid rgba(212,146,12,0.12); border-radius: 12px; padding: 1.5rem;">
            <div style="color: white; font-weight: 700; font-size: 1rem; margin-bottom: 1rem;">Compose Message</div>
            <form wire:submit="send">
                {{ $this->form }}

                <div style="margin-top: 1.5rem; text-align: right;">
                    <button type="submit" style="background: #d4920c; color: #1c1410; border: none; padding: 0.6rem 1.5rem; border-radius: 8px; font-weight: 700; cursor: pointer; font-size: 0.9rem;">
                        Send Message
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-filament-panels::page>
