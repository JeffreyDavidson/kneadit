<x-filament-panels::page>
    <style>
        .config-container { max-width: 720px; margin: 0 auto; }
        .config-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 12px; }
        .config-header p { color: #8b6844; font-size: 0.9rem; margin: 0; }
        .config-actions { display: flex; gap: 8px; }

        .widget-list { display: flex; flex-direction: column; gap: 6px; }

        .widget-card {
            display: flex; align-items: center; gap: 16px;
            padding: 16px 20px; border-radius: 12px;
            border: 1px solid rgba(212,165,116,0.25);
            background: #fdf8f2;
            cursor: grab; user-select: none;
            transition: box-shadow 0.2s, border-color 0.2s, opacity 0.2s;
        }
        .widget-card:hover { border-color: #d4a574; box-shadow: 0 2px 8px rgba(61,35,20,0.08); }
        .widget-card:active { cursor: grabbing; }
        .widget-card.disabled { opacity: 0.45; background: #f5f5f5; border-style: dashed; }
        .widget-card.sortable-ghost { opacity: 0.3; background: #f5e6d0; }
        .widget-card.sortable-drag { box-shadow: 0 8px 24px rgba(61,35,20,0.15); }

        .widget-drag { color: #c4a882; flex-shrink: 0; }
        .widget-icon { font-size: 1.4rem; flex-shrink: 0; width: 32px; text-align: center; }
        .widget-info { flex: 1; min-width: 0; }
        .widget-name { font-weight: 600; color: #3d2314; margin: 0; font-size: 0.95rem; }
        .widget-desc { font-size: 0.8rem; color: #8b6844; margin: 2px 0 0; }
        .widget-pos { font-size: 0.7rem; color: #c4a882; font-family: monospace; min-width: 20px; text-align: center; }

        .toggle-btn {
            position: relative; display: inline-flex; height: 26px; width: 48px;
            align-items: center; border-radius: 13px; border: none; cursor: pointer;
            transition: background 0.2s; flex-shrink: 0;
        }
        .toggle-btn.on { background: #d4a574; }
        .toggle-btn.off { background: #ccc; }
        .toggle-btn span {
            display: inline-block; height: 20px; width: 20px; border-radius: 50%;
            background: white; box-shadow: 0 1px 3px rgba(0,0,0,0.15);
            transition: transform 0.2s;
        }
        .toggle-btn.on span { transform: translateX(24px); }
        .toggle-btn.off span { transform: translateX(3px); }

        .config-footer {
            display: flex; align-items: center; justify-content: space-between;
            padding-top: 20px; margin-top: 24px;
            border-top: 1px solid rgba(212,165,116,0.2);
        }
        .config-footer a {
            font-size: 0.9rem; color: #8b6844; text-decoration: none;
            display: flex; align-items: center; gap: 6px;
        }
        .config-footer a:hover { color: #3d2314; }
    </style>

    <div class="config-container">
        <div class="config-header">
            <p>Drag to reorder. Toggle to show or hide widgets.</p>
            <div class="config-actions">
                <x-filament::button color="gray" wire:click="resetDefaults">
                    Reset Defaults
                </x-filament::button>
                <x-filament::button wire:click="save">
                    Save Layout
                </x-filament::button>
            </div>
        </div>

        <div class="widget-list" id="widget-sortable">
            @foreach($widgets as $index => $widget)
                <div class="widget-card {{ $widget['visible'] ? '' : 'disabled' }}" data-index="{{ $index }}">
                    <div class="widget-drag">
                        <svg xmlns="http://www.w3.org/2000/svg" style="width: 18px; height: 18px;" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M7 2a2 2 0 1 0 0 4 2 2 0 0 0 0-4zM7 8a2 2 0 1 0 0 4 2 2 0 0 0 0-4zM7 14a2 2 0 1 0 0 4 2 2 0 0 0 0-4zM13 2a2 2 0 1 0 0 4 2 2 0 0 0 0-4zM13 8a2 2 0 1 0 0 4 2 2 0 0 0 0-4zM13 14a2 2 0 1 0 0 4 2 2 0 0 0 0-4z"/>
                        </svg>
                    </div>
                    <div class="widget-icon">{{ $widget['icon'] }}</div>
                    <div class="widget-info">
                        <p class="widget-name">{{ $widget['name'] }}</p>
                        <p class="widget-desc">{{ $widget['description'] }}</p>
                    </div>
                    <div class="widget-pos">#{{ $index + 1 }}</div>
                    <button class="toggle-btn {{ $widget['visible'] ? 'on' : 'off' }}"
                            wire:click="toggleWidget({{ $index }})" type="button">
                        <span></span>
                    </button>
                </div>
            @endforeach
        </div>

        <div class="config-footer">
            <a href="{{ route('filament.admin.pages.dashboard') }}">
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
            const el = document.getElementById('widget-sortable');
            if (!el) return;

            Sortable.create(el, {
                animation: 200,
                ghostClass: 'sortable-ghost',
                dragClass: 'sortable-drag',
                onEnd: function(evt) {
                    if (evt.oldIndex === evt.newIndex) return;
                    @this.call('reorder', evt.oldIndex, evt.newIndex);
                }
            });
        });
    </script>
</x-filament-panels::page>
