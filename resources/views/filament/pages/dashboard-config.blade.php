<x-filament-panels::page>
    <div style="display: flex; flex-direction: column; gap: 24px;">
        {{-- Header --}}
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <p style="color: var(--brand-500); font-size: 0.9rem;">
                Choose which widgets appear on your dashboard and arrange them in your preferred order.
            </p>
            <div style="display: flex; gap: 8px;">
                <x-filament::button color="gray" wire:click="resetDefaults">
                    Reset to Defaults
                </x-filament::button>
                <x-filament::button wire:click="save">
                    Save Layout
                </x-filament::button>
            </div>
        </div>

        {{-- Widget Cards --}}
        <div style="display: flex; flex-direction: column; gap: 8px;">
            @foreach($widgets as $index => $widget)
                <div style="display: flex; align-items: center; gap: 16px; padding: 16px; border-radius: 12px; border: 1px solid {{ $widget['visible'] ? 'rgba(212,165,116,0.25)' : 'rgba(212,165,116,0.1)' }}; background: {{ $widget['visible'] ? 'var(--brand-50, #fdf8f2)' : '#f5f5f5' }}; {{ $widget['visible'] ? '' : 'opacity: 0.5;' }}">
                    {{-- Order arrows --}}
                    <div style="display: flex; flex-direction: column; gap: 2px;">
                        @if($index > 0)
                            <button wire:click="moveUp({{ $index }})" style="background: none; border: none; cursor: pointer; color: var(--brand-500); padding: 2px;">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" style="width: 20px; height: 20px;"><path fill-rule="evenodd" d="M9.47 6.47a.75.75 0 0 1 1.06 0l4.25 4.25a.75.75 0 1 1-1.06 1.06L10 8.06l-3.72 3.72a.75.75 0 0 1-1.06-1.06l4.25-4.25Z" clip-rule="evenodd" /></svg>
                            </button>
                        @else
                            <div style="width: 20px; height: 24px;"></div>
                        @endif
                        @if($index < count($widgets) - 1)
                            <button wire:click="moveDown({{ $index }})" style="background: none; border: none; cursor: pointer; color: var(--brand-500); padding: 2px;">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" style="width: 20px; height: 20px;"><path fill-rule="evenodd" d="M10.53 13.53a.75.75 0 0 1-1.06 0l-4.25-4.25a.75.75 0 0 1 1.06-1.06L10 11.94l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25Z" clip-rule="evenodd" /></svg>
                            </button>
                        @else
                            <div style="width: 20px; height: 24px;"></div>
                        @endif
                    </div>

                    {{-- Icon --}}
                    <div style="font-size: 1.5rem; flex-shrink: 0; width: 40px; text-align: center;">
                        {{ $widget['icon'] }}
                    </div>

                    {{-- Info --}}
                    <div style="flex: 1; min-width: 0;">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <h3 style="font-weight: 600; color: var(--brand-900); margin: 0;">
                                {{ $widget['name'] }}
                            </h3>
                            <span style="font-size: 0.75rem; color: var(--brand-400); font-family: monospace;">
                                #{{ $widget['order'] }}
                            </span>
                        </div>
                        <p style="font-size: 0.85rem; color: var(--brand-500); margin: 4px 0 0 0;">
                            {{ $widget['description'] }}
                        </p>
                    </div>

                    {{-- Toggle --}}
                    <div style="flex-shrink: 0;">
                        <button
                            wire:click="toggleWidget({{ $index }})"
                            style="position: relative; display: inline-flex; height: 24px; width: 44px; align-items: center; border-radius: 12px; border: none; cursor: pointer; transition: background 0.2s; background: {{ $widget['visible'] ? 'var(--brand-300, #d4a574)' : '#ccc' }};"
                        >
                            <span style="display: inline-block; height: 18px; width: 18px; border-radius: 50%; background: white; transition: transform 0.2s; transform: translateX({{ $widget['visible'] ? '22px' : '3px' }});"></span>
                        </button>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Bottom save --}}
        <div style="display: flex; align-items: center; justify-content: space-between; padding-top: 16px; border-top: 1px solid rgba(212,165,116,0.2);">
            <a href="{{ route('filament.admin.pages.dashboard') }}" style="font-size: 0.9rem; color: var(--brand-500); text-decoration: none; display: flex; align-items: center; gap: 6px;">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" style="width: 16px; height: 16px;"><path fill-rule="evenodd" d="M11.78 5.22a.75.75 0 0 1 0 1.06L8.06 10l3.72 3.72a.75.75 0 1 1-1.06 1.06l-4.25-4.25a.75.75 0 0 1 0-1.06l4.25-4.25a.75.75 0 0 1 1.06 0Z" clip-rule="evenodd" /></svg>
                Back to Dashboard
            </a>
            <x-filament::button wire:click="save">
                Save Layout
            </x-filament::button>
        </div>
    </div>
</x-filament-panels::page>
