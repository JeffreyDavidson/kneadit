<x-filament-panels::page>
    <style @cspnonce>
        .config-shell {
            background: #f5f0ea;
            border: 1px solid rgba(212, 165, 116, 0.15);
            border-radius: 16px;
            padding: 20px;
        }
        .config-shell-header {
            display: flex; justify-content: space-between; align-items: center;
            margin-bottom: 16px; padding-bottom: 12px;
            border-bottom: 1px solid rgba(212, 165, 116, 0.15);
        }
        .config-shell-title {
            font-size: 0.85rem; font-weight: 700; color: #3d2314;
            text-transform: uppercase; letter-spacing: 0.5px; margin: 0;
        }
        .config-shell-hint {
            font-size: 0.7rem; color: #8b6844;
        }

        /* Unified grid: each tile occupies its size's column-span. */
        .preview-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 8px;
            min-height: 400px;
            grid-auto-rows: minmax(110px, auto);
        }

        /* Common widget tile look (used in both config and read-only preview). */
        .preview-widget {
            border-radius: 10px; overflow: hidden;
            border: 1px solid rgba(212, 165, 116, 0.15);
            background: white;
            transition: opacity 0.2s, box-shadow 0.2s;
            position: relative;
        }
        .preview-widget-header {
            background: linear-gradient(135deg, #6b4c3b, #8b6844);
            padding: 8px 12px;
            display: flex; align-items: center; gap: 6px;
        }
        .preview-widget-header span { color: white; font-size: 0.75rem; font-weight: 600; }
        .preview-widget-header .pw-icon { font-size: 0.85rem; }
        .preview-widget-body { padding: 12px; min-height: 50px; }

        /* Config-mode tile: drag affordance + dimmed when hidden. */
        .config-tile { cursor: grab; user-select: none; }
        .config-tile:active { cursor: grabbing; }
        .config-tile.is-hidden { opacity: 0.4; }
        .config-tile.sortable-ghost { opacity: 0.15; }
        .config-tile.sortable-drag { box-shadow: 0 8px 24px rgba(61, 35, 20, 0.18); z-index: 20; opacity: 1; }

        /* Controls overlay — visible on hover/focus-within. */
        .config-controls {
            position: absolute; top: 6px; left: 6px; right: 6px;
            display: flex; align-items: center; gap: 6px;
            opacity: 0; transition: opacity 0.15s;
            z-index: 10;
        }
        .config-tile:hover .config-controls,
        .config-tile:focus-within .config-controls,
        .config-tile.is-hidden .config-controls { opacity: 1; }

        .config-ctrl {
            width: 26px; height: 26px;
            display: inline-flex; align-items: center; justify-content: center;
            border-radius: 6px; border: none; cursor: pointer;
            background: rgba(255, 255, 255, 0.9);
            color: #6b4c3b;
            backdrop-filter: blur(4px);
            transition: background 0.15s, color 0.15s;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
        }
        .config-ctrl:hover { background: white; color: #3d2314; }
        .config-drag { cursor: grab; }
        .config-toggle.is-on { color: #6b4c3b; }
        .config-toggle.is-off { color: #b97a52; background: rgba(255, 240, 230, 0.95); }

        .config-size-group {
            display: flex; gap: 1px; flex: 1;
            background: rgba(212, 165, 116, 0.2);
            border-radius: 6px; overflow: hidden;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
        }
        .config-size-btn {
            flex: 1; height: 26px; border: none; cursor: pointer;
            background: rgba(255, 255, 255, 0.9);
            color: #a08060; font-size: 0.65rem; font-weight: 700;
            transition: background 0.15s, color 0.15s;
        }
        .config-size-btn:hover { background: white; color: #6b4c3b; }
        .config-size-btn.active { background: #d4a574; color: white; }

        .config-size-locked {
            flex: 1; height: 26px;
            display: flex; align-items: center; justify-content: center;
            background: rgba(212, 165, 116, 0.15);
            color: #a08060; font-size: 0.65rem; font-weight: 700;
            border-radius: 6px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
        }

        /* Push the widget header down so controls don't sit on top of the title. */
        .config-tile .preview-widget-header { padding-top: 38px; }

        /* Preview content styles (carry-over from before). */
        .pw-stat { display: flex; justify-content: space-between; align-items: baseline; }
        .pw-stat-value { font-size: 1.4rem; font-weight: 700; color: #3d2314; }
        .pw-stat-label { font-size: 0.65rem; color: #a08060; text-transform: uppercase; }
        .pw-bar { height: 6px; border-radius: 3px; background: rgba(212, 165, 116, 0.15); margin-top: 6px; }
        .pw-bar-fill { height: 100%; border-radius: 3px; background: linear-gradient(90deg, #d4a574, #e8b04a); }
        .pw-line { height: 40px; display: flex; align-items: end; gap: 2px; }
        .pw-line-bar { flex: 1; background: rgba(212, 165, 116, 0.3); border-radius: 2px 2px 0 0; min-height: 4px; }
        .pw-row { display: flex; justify-content: space-between; padding: 4px 0; border-bottom: 1px solid rgba(212, 165, 116, 0.08); font-size: 0.7rem; color: #6b4c3b; }
        .pw-row:last-child { border: none; }
        .pw-dot { width: 6px; height: 6px; border-radius: 50%; display: inline-block; margin-right: 4px; }

        .config-footer {
            display: flex; align-items: center; justify-content: space-between;
            padding-top: 20px; margin-top: 24px;
            border-top: 1px solid rgba(212, 165, 116, 0.2);
        }
        .config-footer a {
            font-size: 0.9rem; color: #8b6844; text-decoration: none;
            display: flex; align-items: center; gap: 6px;
        }
        .config-footer a:hover { color: #3d2314; }
    </style>

    <div class="config-shell">
        <div class="config-shell-header">
            <p class="config-shell-title">Dashboard Layout</p>
            <span class="config-shell-hint">Drag to reorder · Hover a tile to change size or hide it</span>
            <x-filament::button size="xs" color="gray" wire:click="resetDefaults">Reset</x-filament::button>
        </div>

        <div class="preview-grid" id="widget-sortable">
            @foreach ($widgets as $index => $widget)
                @include('filament.shared.dashboard.widget-card', [
                    'widget' => $widget,
                    'configMode' => true,
                    'index' => $index,
                ])
            @endforeach
        </div>
    </div>

    <div class="config-footer">
        <a href="{{ route('filament.admin.pages.dashboard') }}">
            <x-heroicon-s-chevron-left class="w-4 h-4" />
            Back to Dashboard
        </a>
        <x-filament::button wire:click="save">
            Save Layout
        </x-filament::button>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.6/Sortable.min.js"></script>
    <script @cspnonce>
        document.addEventListener('DOMContentLoaded', function () {
            const el = document.getElementById('widget-sortable');
            if (!el) return;

            Sortable.create(el, {
                animation: 200,
                ghostClass: 'sortable-ghost',
                dragClass: 'sortable-drag',
                // Don't initiate drag from the controls overlay — let buttons receive clicks.
                filter: '.config-ctrl, .config-size-btn',
                preventOnFilter: false,
                onEnd: function (evt) {
                    if (evt.oldIndex === evt.newIndex) return;
                    @this.call('reorder', evt.oldIndex, evt.newIndex);
                },
            });
        });
    </script>
</x-filament-panels::page>
