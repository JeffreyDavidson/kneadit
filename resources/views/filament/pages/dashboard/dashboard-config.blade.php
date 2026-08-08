<x-filament-panels::page>
    <style @cspnonce>
        .config-shell {
            background: var(--brand-900);
            border: 1px solid color-mix(in srgb, var(--brand-800) 60%, transparent);
            border-radius: 16px;
            padding: 20px;
        }
        .config-shell-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
            padding-bottom: 12px;
            border-bottom: 1px solid color-mix(in srgb, var(--brand-700) 40%, transparent);
        }
        .config-shell-title {
            font-size: 0.65rem;
            font-weight: 600;
            color: var(--brand-300);
            text-transform: uppercase;
            letter-spacing: 0.1em;
            margin: 0;
        }
        .config-shell-hint {
            font-size: 0.7rem;
            color: var(--brand-400);
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
            border-radius: 10px;
            overflow: hidden;
            border: 1px solid color-mix(in srgb, var(--brand-700) 60%, transparent);
            background: var(--brand-800);
            transition:
                opacity 0.2s,
                box-shadow 0.2s;
            position: relative;
        }
        .preview-widget-header {
            background: color-mix(in srgb, var(--brand-900) 40%, transparent);
            padding: 8px 12px;
            display: flex;
            align-items: center;
            gap: 6px;
            border-bottom: 1px solid color-mix(in srgb, var(--brand-700) 40%, transparent);
        }
        .preview-widget-header span {
            color: var(--brand-100);
            font-size: 0.75rem;
            font-weight: 600;
        }
        .preview-widget-header .pw-icon {
            font-size: 0.85rem;
        }
        .preview-widget-body {
            padding: 12px;
            min-height: 50px;
        }

        /* Config-mode tile: drag affordance + dimmed when hidden. */
        .config-tile {
            cursor: grab;
            user-select: none;
        }
        .config-tile:active {
            cursor: grabbing;
        }
        .config-tile.is-hidden {
            opacity: 0.4;
        }
        .config-tile.sortable-ghost {
            opacity: 0.15;
        }
        .config-tile.sortable-drag {
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.4);
            z-index: 20;
            opacity: 1;
        }

        /* Controls overlay — visible on hover/focus-within. */
        .config-controls {
            position: absolute;
            top: 6px;
            left: 6px;
            right: 6px;
            display: flex;
            align-items: center;
            gap: 6px;
            opacity: 0;
            transition: opacity 0.15s;
            z-index: 10;
        }
        .config-tile:hover .config-controls,
        .config-tile:focus-within .config-controls,
        .config-tile.is-hidden .config-controls {
            opacity: 1;
        }

        .config-ctrl {
            width: 26px;
            height: 26px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
            border: 1px solid color-mix(in srgb, var(--brand-700) 60%, transparent);
            cursor: pointer;
            background: color-mix(in srgb, var(--brand-900) 90%, transparent);
            color: var(--brand-200);
            backdrop-filter: blur(4px);
            transition:
                background 0.15s,
                color 0.15s,
                border-color 0.15s;
        }
        .config-ctrl:hover {
            background: var(--brand-800);
            color: var(--brand-300);
            border-color: color-mix(in srgb, var(--brand-300) 40%, transparent);
        }
        .config-drag {
            cursor: grab;
        }
        .config-toggle.is-on {
            color: var(--brand-300);
        }
        .config-toggle.is-off {
            color: #f87171;
            background: rgba(248, 113, 113, 0.1);
            border-color: rgba(248, 113, 113, 0.25);
        }

        .config-size-group {
            display: flex;
            gap: 1px;
            flex: 1;
            background: color-mix(in srgb, var(--brand-700) 40%, transparent);
            border-radius: 6px;
            overflow: hidden;
            border: 1px solid color-mix(in srgb, var(--brand-700) 60%, transparent);
        }
        .config-size-btn {
            flex: 1;
            height: 26px;
            border: none;
            cursor: pointer;
            background: color-mix(in srgb, var(--brand-900) 90%, transparent);
            color: var(--brand-400);
            font-size: 0.65rem;
            font-weight: 700;
            transition:
                background 0.15s,
                color 0.15s;
        }
        .config-size-btn:hover {
            background: var(--brand-800);
            color: var(--brand-200);
        }
        .config-size-btn.active {
            background: var(--brand-300);
            color: var(--brand-900);
        }

        .config-size-locked {
            flex: 1;
            height: 26px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: color-mix(in srgb, var(--brand-700) 30%, transparent);
            color: var(--brand-400);
            font-size: 0.65rem;
            font-weight: 700;
            border-radius: 6px;
            border: 1px solid color-mix(in srgb, var(--brand-700) 60%, transparent);
        }

        /* Push the widget header down so controls don't sit on top of the title. */
        .config-tile .preview-widget-header {
            padding-top: 38px;
        }

        /* Preview content styles — match the live dashboard's brand-800 chrome. */
        .pw-stat {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
        }
        .pw-stat-value {
            font-size: 1.4rem;
            font-weight: 700;
            color: #fff;
        }
        .pw-stat-label {
            font-size: 0.65rem;
            color: var(--brand-400);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .pw-bar {
            height: 6px;
            border-radius: 3px;
            background: color-mix(in srgb, var(--brand-700) 40%, transparent);
            margin-top: 6px;
        }
        .pw-bar-fill {
            height: 100%;
            border-radius: 3px;
            background: var(--brand-300);
        }
        .pw-line {
            height: 40px;
            display: flex;
            align-items: end;
            gap: 2px;
        }
        .pw-line-bar {
            flex: 1;
            background: color-mix(in srgb, var(--brand-300) 50%, transparent);
            border-radius: 2px 2px 0 0;
            min-height: 4px;
        }
        .pw-row {
            display: flex;
            justify-content: space-between;
            padding: 4px 0;
            border-bottom: 1px solid color-mix(in srgb, var(--brand-700) 30%, transparent);
            font-size: 0.7rem;
            color: var(--brand-100);
        }
        .pw-row:last-child {
            border: none;
        }
        .pw-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 4px;
        }

        .config-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-top: 20px;
            margin-top: 24px;
            border-top: 1px solid color-mix(in srgb, var(--brand-800) 60%, transparent);
        }
        .config-footer a {
            font-size: 0.9rem;
            color: var(--brand-300);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .config-footer a:hover {
            color: var(--brand-200);
        }
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
            <x-heroicon-s-chevron-left class="h-4 w-4" />
            Back to Dashboard
        </a>
        <x-filament::button wire:click="save"> Save Layout </x-filament::button>
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
