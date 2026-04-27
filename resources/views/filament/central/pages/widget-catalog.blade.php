<x-filament-panels::page>
    <style @cspnonce>
        /* The partial styling rules below are scoped page-wide (no
           parent class prefix) so they apply both in default-mode tiles
           — wrapped in .catalog-grid — AND in all-sizes mode tiles
           which render inside .catalog-row-cell. The <style> block is
           page-scoped (only loaded when the catalog page renders), so
           there's no risk of leaking into other pages that use the
           same .preview-widget partial (e.g. tenant DashboardConfig). */

        .catalog-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; }

        .preview-widget {
            border-radius: 12px; overflow: hidden;
            background: var(--platform-900);
            border: 1px solid var(--border-medium);
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
        }
        .preview-widget-header {
            background: linear-gradient(135deg, var(--platform-800), var(--platform-700));
            border-bottom: 1px solid var(--border-subtle);
            padding: 8px 12px;
            display: flex; align-items: center; gap: 6px;
        }
        .preview-widget-header span { color: var(--accent); font-size: 0.75rem; font-weight: 600; }
        .preview-widget-header .pw-icon { font-size: 0.85rem; }
        .preview-widget-body { padding: 14px; min-height: 60px; color: var(--platform-200); }

        /* Partial's structural row/stat/bar/line classes — unprefixed so
           they fire in all-sizes mode too. */
        .pw-stat { display: flex; justify-content: space-between; align-items: baseline; }
        .pw-stat-value { font-size: 1.4rem; font-weight: 700; color: var(--platform-100); }
        .pw-stat-label { font-size: 0.65rem; color: var(--platform-400); text-transform: uppercase; }
        .pw-bar { height: 6px; border-radius: 3px; background: var(--border-subtle); margin-top: 6px; }
        .pw-bar-fill { height: 100%; border-radius: 3px; background: linear-gradient(90deg, var(--accent), var(--accent-light)); }
        .pw-line { height: 40px; display: flex; align-items: end; gap: 2px; }
        .pw-line-bar { flex: 1; background: var(--border-medium); border-radius: 2px 2px 0 0; min-height: 4px; }
        .pw-row {
            display: flex; justify-content: space-between;
            padding: 4px 0;
            border-bottom: 1px solid var(--border-subtle);
            font-size: 0.7rem; color: var(--platform-300);
            gap: 8px;
        }
        .pw-row:last-child { border: none; }
        .pw-dot { width: 6px; height: 6px; border-radius: 50%; display: inline-block; margin-right: 4px; }

        /* Force-recolor inline styles in the partial body. */
        .preview-widget-body [style*="color: #3d2314"] { color: var(--platform-100) !important; }
        .preview-widget-body [style*="color: #6b4c3b"] { color: var(--platform-200) !important; }
        .preview-widget-body [style*="color: #a08060"] { color: var(--platform-400) !important; }
        .preview-widget-body [style*="color: #8b6844"] { color: var(--accent) !important; }
        .preview-widget-body [style*="background: #fdf8f2"] { background: var(--platform-800) !important; }

        /* All-sizes mode — one row per widget, three tiles inside (sm/md/lg
           where allowed). Each row is its own mini 3-column grid so the tiles
           inside lay out at their natural span. */
        .catalog-row {
            background: var(--platform-900);
            border: 1px solid var(--border-medium);
            border-radius: 12px;
            padding: 14px;
            margin-bottom: 14px;
        }
        .catalog-row-header {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            margin-bottom: 12px;
            padding-bottom: 8px;
            border-bottom: 1px solid var(--border-subtle);
        }
        .catalog-row-name { color: var(--platform-100); font-weight: 600; font-size: 0.95rem; }
        .catalog-row-key { color: var(--platform-400); font-size: 0.7rem; font-family: monospace; }
        .catalog-row-grid {
            display: grid;
            gap: 8px;
            /* grid-template-columns is set inline based on the number of
               allowed sizes — each variant gets equal width so SM/MD/LG
               can be compared directly. */
        }
        .catalog-row-cell { position: relative; }
        .catalog-size-label {
            position: absolute; top: 4px; right: 6px;
            font-size: 0.55rem; font-weight: 700;
            background: rgba(0, 0, 0, 0.5);
            color: var(--accent);
            padding: 2px 5px; border-radius: 4px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            z-index: 1;
        }
    </style>

    <div class="mb-6 flex items-start justify-between gap-4">
        <p class="text-sm text-cinnamon flex-1">
            Representative thumbnails of every tenant widget at its default size.
            Use this page to review new widgets and their layouts before bakery owners see them.
            Each tile uses the same partial that powers the bakery dashboard configurator.
        </p>
        <x-filament::button size="sm" color="gray" wire:click="toggleAllSizes">
            {{ $showAllSizes ? 'Show defaults' : 'Show all sizes' }}
        </x-filament::button>
    </div>

    @if ($showAllSizes)
        @foreach ($this->catalogWidgets as $widget)
            <div class="catalog-row">
                <div class="catalog-row-header">
                    <span class="catalog-row-name">{{ $widget['name'] }}</span>
                    <span class="catalog-row-key">{{ $widget['key'] }}</span>
                </div>
                <div class="catalog-row-grid"
                     style="grid-template-columns: repeat({{ count($widget['allowedSizes']) }}, 1fr);">
                    @foreach ($widget['allowedSizes'] as $size)
                        <div class="catalog-row-cell">
                            <span class="catalog-size-label">{{ $size }}</span>
                            @include('filament.shared.dashboard.widget-card', [
                                'widget' => array_merge($widget, ['size' => $size]),
                            ])
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    @else
        <div class="catalog-grid">
            @foreach ($this->catalogWidgets as $widget)
                @include('filament.shared.dashboard.widget-card', ['widget' => $widget])
            @endforeach
        </div>
    @endif
</x-filament-panels::page>
