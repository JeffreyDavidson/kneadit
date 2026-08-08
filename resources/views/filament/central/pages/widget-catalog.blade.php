<x-filament-panels::page>
    <style @cspnonce>
        /* Card primitives (.preview-widget, .pw-*) live in
           public/css/widget-cards.css and are loaded panel-wide. The
           rules below are catalog-specific layout + the inline-style
           recolor overrides for hardcoded thumbnail colors. */

        .catalog-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
        }

        /* Force-recolor inline-style hex colors in thumbnails to land
           on central panel tokens. Thumbnails were originally written
           against tenant brand colors; this layer maps them. */
        .preview-widget-body [style*='color: #3d2314'] {
            color: var(--platform-100) !important;
        }
        .preview-widget-body [style*='color: #6b4c3b'] {
            color: var(--platform-200) !important;
        }
        .preview-widget-body [style*='color: #a08060'] {
            color: var(--platform-400) !important;
        }
        .preview-widget-body [style*='color: #8b6844'] {
            color: var(--accent) !important;
        }
        .preview-widget-body [style*='background: #fdf8f2'] {
            background: var(--platform-800) !important;
        }

        /* All-sizes mode — one row per widget, tiles inside (sm/md/lg/xl
           where allowed). Each row is its own mini grid so the tiles
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
        .catalog-row-name {
            color: var(--platform-100);
            font-weight: 600;
            font-size: 0.95rem;
        }
        .catalog-row-key {
            color: var(--platform-400);
            font-size: 0.7rem;
            font-family: monospace;
        }
        .catalog-row-grid {
            display: grid;
            gap: 8px;
            /* grid-template-columns is set inline based on the number of
               allowed sizes — each variant gets equal width so SM/MD/LG
               can be compared directly. */
        }
        .catalog-row-cell {
            position: relative;
        }
        .catalog-size-label {
            position: absolute;
            top: 4px;
            right: 6px;
            font-size: 0.55rem;
            font-weight: 700;
            background: rgba(0, 0, 0, 0.5);
            color: var(--accent);
            padding: 2px 5px;
            border-radius: 4px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            z-index: 1;
        }
    </style>

    <div class="mb-6 flex items-start justify-between gap-4">
        <p class="text-cinnamon flex-1 text-sm">
            Representative thumbnails of every tenant widget at its default size. Use this page to review new widgets
            and their layouts before bakery owners see them. Each tile uses the same partial that powers the bakery
            dashboard configurator.
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
                <div
                    class="catalog-row-grid"
                    style="grid-template-columns: repeat({{ count($widget['allowedSizes']) }}, 1fr);"
                >
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
