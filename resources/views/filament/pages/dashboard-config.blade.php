<x-filament-panels::page>
    <div style="display: flex; flex-direction: column; gap: 24px;">
        {{-- Header --}}
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
            <p style="color: var(--brand-500); font-size: 0.9rem; margin: 0;">
                Drag to reorder. Toggle to show or hide widgets on your dashboard.
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

        {{-- Widget Cards (sortable) --}}
        <div id="widget-list" style="display: flex; flex-direction: column; gap: 8px;">
            @foreach($widgets as $index => $widget)
                <div
                    data-index="{{ $index }}"
                    style="display: flex; align-items: center; gap: 16px; padding: 14px 18px; border-radius: 12px; border: 1px solid {{ $widget['visible'] ? 'rgba(212,165,116,0.25)' : 'rgba(212,165,116,0.1)' }}; background: {{ $widget['visible'] ? 'var(--brand-50, #fdf8f2)' : '#f5f5f5' }}; {{ $widget['visible'] ? '' : 'opacity: 0.5;' }} cursor: grab; user-select: none; transition: box-shadow 0.2s, opacity 0.2s;"
                    onmouseover="this.style.boxShadow='0 2px 8px rgba(61,35,20,0.1)'"
                    onmouseout="this.style.boxShadow='none'"
                >
                    {{-- Drag handle --}}
                    <div style="color: var(--brand-400); flex-shrink: 0; cursor: grab;">
                        <svg xmlns="http://www.w3.org/2000/svg" style="width: 20px; height: 20px;" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M7 2a2 2 0 1 0 0 4 2 2 0 0 0 0-4zM7 8a2 2 0 1 0 0 4 2 2 0 0 0 0-4zM7 14a2 2 0 1 0 0 4 2 2 0 0 0 0-4zM13 2a2 2 0 1 0 0 4 2 2 0 0 0 0-4zM13 8a2 2 0 1 0 0 4 2 2 0 0 0 0-4zM13 14a2 2 0 1 0 0 4 2 2 0 0 0 0-4z"/>
                        </svg>
                    </div>

                    {{-- Icon --}}
                    <div style="font-size: 1.4rem; flex-shrink: 0; width: 36px; text-align: center;">
                        {{ $widget['icon'] }}
                    </div>

                    {{-- Info --}}
                    <div style="flex: 1; min-width: 0;">
                        <h3 style="font-weight: 600; color: var(--brand-900); margin: 0; font-size: 0.95rem;">
                            {{ $widget['name'] }}
                        </h3>
                        <p style="font-size: 0.8rem; color: var(--brand-500); margin: 2px 0 0 0;">
                            {{ $widget['description'] }}
                        </p>
                    </div>

                    {{-- Toggle --}}
                    <div style="flex-shrink: 0;">
                        <button
                            wire:click="toggleWidget({{ $index }})"
                            style="position: relative; display: inline-flex; height: 26px; width: 48px; align-items: center; border-radius: 13px; border: none; cursor: pointer; transition: background 0.2s; background: {{ $widget['visible'] ? 'var(--brand-300, #d4a574)' : '#ccc' }};"
                        >
                            <span style="display: inline-block; height: 20px; width: 20px; border-radius: 50%; background: white; box-shadow: 0 1px 3px rgba(0,0,0,0.15); transition: transform 0.2s; transform: translateX({{ $widget['visible'] ? '24px' : '3px' }});"></span>
                        </button>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Bottom --}}
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

    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.6/Sortable.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const el = document.getElementById('widget-list');
            if (!el) return;

            Sortable.create(el, {
                animation: 200,
                ghostClass: 'sortable-ghost',
                handle: 'div', // entire row is draggable
                onEnd: function(evt) {
                    const oldIndex = evt.oldIndex;
                    const newIndex = evt.newIndex;
                    if (oldIndex === newIndex) return;

                    // Call Livewire to reorder
                    @this.call('reorder', oldIndex, newIndex);
                }
            });
        });

        // Re-init after Livewire updates
        document.addEventListener('livewire:navigated', function() {
            setTimeout(function() {
                const el = document.getElementById('widget-list');
                if (!el || el._sortable) return;
                Sortable.create(el, {
                    animation: 200,
                    onEnd: function(evt) {
                        if (evt.oldIndex === evt.newIndex) return;
                        @this.call('reorder', evt.oldIndex, evt.newIndex);
                    }
                });
            }, 100);
        });
    </script>

    <style>
        .sortable-ghost {
            opacity: 0.4 !important;
            background: var(--brand-100, #f5e6d0) !important;
        }
    </style>
</x-filament-panels::page>
