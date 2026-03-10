<x-filament-panels::page>
    <div style="max-width: 700px; margin: 0 auto;">
        <div style="background: #1c1410; border: 1px solid rgba(212,146,12,0.12); border-radius: 12px; padding: 1.5rem;">
            <div style="color: white; font-weight: 700; font-size: 1rem; margin-bottom: 0.25rem;">Compose Message</div>
            <div style="color: #8b6844; font-size: 0.85rem; margin-bottom: 1.25rem;">Send a direct message to a bakery tenant.</div>
            <form wire:submit="send">
                {{ $this->form }}

                <div style="margin-top: 1.5rem; display: flex; justify-content: flex-end; gap: 0.75rem;">
                    <a href="{{ \App\Filament\Central\Resources\MessageResource::getUrl('index') }}"
                       style="display: inline-flex; align-items: center; padding: 0.6rem 1.25rem; background: #2a1f18; color: #faf0d6; border: 1px solid rgba(212,146,12,0.12); border-radius: 8px; font-weight: 600; font-size: 0.85rem; text-decoration: none;">
                        Cancel
                    </a>
                    <button type="submit" style="background: #d4920c; color: #1c1410; border: none; padding: 0.6rem 1.5rem; border-radius: 8px; font-weight: 700; cursor: pointer; font-size: 0.9rem;">
                        Send Message
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-filament-panels::page>
