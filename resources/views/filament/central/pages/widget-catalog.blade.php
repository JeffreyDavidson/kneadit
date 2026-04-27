<x-filament-panels::page>
    <style @cspnonce>
        /* Override the partial's tenant-light styling for the central dark panel. */
        .catalog-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; }
        .catalog-grid .preview-widget {
            border-radius: 12px; overflow: hidden;
            background: var(--platform-900);
            border: 1px solid var(--border-medium);
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
        }
        .catalog-grid .preview-widget-header {
            background: linear-gradient(135deg, var(--platform-800), var(--platform-700));
            border-bottom: 1px solid var(--border-subtle);
            padding: 8px 12px;
            display: flex; align-items: center; gap: 6px;
        }
        .catalog-grid .preview-widget-header span { color: var(--accent); font-size: 0.75rem; font-weight: 600; }
        .catalog-grid .preview-widget-header .pw-icon { font-size: 0.85rem; }
        .catalog-grid .preview-widget-body { padding: 14px; min-height: 60px; color: var(--platform-200); }

        /* Re-skin the partial's hardcoded inline styles. The partial uses
           inline styles (e.g. color: #3d2314) that we can't directly override,
           so target the structural classes and rely on inheritance for inline
           color values where possible. */
        .catalog-grid .pw-stat { display: flex; justify-content: space-between; align-items: baseline; }
        .catalog-grid .pw-stat-value { font-size: 1.4rem; font-weight: 700; color: var(--platform-100); }
        .catalog-grid .pw-stat-label { font-size: 0.65rem; color: var(--platform-400); text-transform: uppercase; }
        .catalog-grid .pw-bar { height: 6px; border-radius: 3px; background: var(--border-subtle); margin-top: 6px; }
        .catalog-grid .pw-bar-fill { height: 100%; border-radius: 3px; background: linear-gradient(90deg, var(--accent), var(--accent-light)); }
        .catalog-grid .pw-line { height: 40px; display: flex; align-items: end; gap: 2px; }
        .catalog-grid .pw-line-bar { flex: 1; background: var(--border-medium); border-radius: 2px 2px 0 0; min-height: 4px; }
        .catalog-grid .pw-row {
            display: flex; justify-content: space-between;
            padding: 4px 0;
            border-bottom: 1px solid var(--border-subtle);
            font-size: 0.7rem; color: var(--platform-300);
        }
        .catalog-grid .pw-row:last-child { border: none; }
        .catalog-grid .pw-dot { width: 6px; height: 6px; border-radius: 50%; display: inline-block; margin-right: 4px; }

        /* Force-recolor inline styles in the partial body. The partial has lots of
           hardcoded #3d2314 / #a08060 / etc. via style="" attributes. We catch
           the most common ones with attribute selectors so the dark theme reads. */
        .catalog-grid .preview-widget-body [style*="color: #3d2314"] { color: var(--platform-100) !important; }
        .catalog-grid .preview-widget-body [style*="color: #6b4c3b"] { color: var(--platform-200) !important; }
        .catalog-grid .preview-widget-body [style*="color: #a08060"] { color: var(--platform-400) !important; }
        .catalog-grid .preview-widget-body [style*="color: #8b6844"] { color: var(--accent) !important; }
        .catalog-grid .preview-widget-body [style*="background: #fdf8f2"] { background: var(--platform-800) !important; }
    </style>

    <div class="mb-6 text-sm text-cinnamon">
        Representative thumbnails of every tenant widget at its default size.
        Use this page to review new widgets and their layouts before bakery owners see them.
        Each tile uses the same partial that powers the bakery dashboard configurator.
    </div>

    <div class="catalog-grid">
        @foreach ($this->catalogWidgets as $widget)
            @include('filament.shared.dashboard.widget-card', ['widget' => $widget])
        @endforeach
    </div>
</x-filament-panels::page>
