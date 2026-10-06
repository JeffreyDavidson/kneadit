<x-filament-panels::page>
    <style @cspnonce>
        /* Card primitives (.preview-widget, .pw-*) live in
           resources/css/filament/shared/widget-cards.css. The rules below are
           catalog-specific layout, on the design system tokens. */

        .catalog-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
        }

        /* All-sizes mode: one row per widget, tiles inside (sm/md/lg/xl
           where allowed). Each row is its own mini grid so the tiles
           inside lay out at their natural span. */
        .catalog-row {
            background: var(--kn-surface);
            border: 1px solid var(--kn-border);
            border-radius: var(--kn-radius-lg);
            box-shadow: var(--kn-shadow-card);
            padding: 14px;
            margin-bottom: 14px;
        }
        .catalog-row-header {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            margin-bottom: 12px;
            padding-bottom: 8px;
            border-bottom: 1px solid var(--kn-border);
        }
        .catalog-row-name {
            color: var(--kn-ink);
            font-weight: 600;
            font-size: 0.95rem;
        }
        .catalog-row-key {
            color: var(--kn-muted);
            font-size: 0.7rem;
            font-family: var(--kn-font-mono);
        }
        .catalog-row-grid {
            display: grid;
            gap: 8px;
            /* grid-template-columns is set inline based on the number of
               allowed sizes: each variant gets equal width so SM/MD/LG
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
            background: var(--kn-espresso);
            color: var(--kn-on-espresso);
            padding: 2px 5px;
            border-radius: var(--kn-radius-sm);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            z-index: 1;
        }
    </style>

    <div class="mb-6 flex items-start justify-between gap-4">
        <p class="flex-1 text-sm text-(--kn-muted)">
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
                            <x-filament.widgets.widget-card :widget="array_merge($widget, ['size' => $size])" />
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    @else
        <div class="catalog-grid">
            @foreach ($this->catalogWidgets as $widget)
                <x-filament.widgets.widget-card :widget="$widget" />
            @endforeach
        </div>
    @endif
</x-filament-panels::page>
