<x-filament-panels::page>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/gridstack@10.3.1/dist/gridstack.min.css">

    <style>
        .grid-stack { min-height: 200px; }

        /* Widget fills the entire grid cell */
        .grid-stack-item-content {
            border-radius: 12px;
            overflow: hidden !important;
            cursor: default !important;
            inset: 0 !important;
        }
        .grid-stack-item { cursor: default !important; }

        /* Make Filament widget wrappers fill cell height */
        .grid-stack-item-content > div,
        .grid-stack-item-content .fi-wi,
        .grid-stack-item-content .fi-wi > div,
        .grid-stack-item-content .fi-section,
        .grid-stack-item-content .fi-section-content-ctn {
            height: 100%;
        }

        /* Section content should scroll if overflow */
        .grid-stack-item-content .fi-section-content-ctn {
            overflow-y: auto;
        }

        /* Stats overview needs auto height since it has multiple rows */
        .grid-stack-item[gs-id="stats_overview"] .grid-stack-item-content > div,
        .grid-stack-item[gs-id="stats_overview"] .fi-wi,
        .grid-stack-item[gs-id="welcome_banner"] .grid-stack-item-content > div,
        .grid-stack-item[gs-id="welcome_banner"] .fi-wi {
            height: auto;
        }
    </style>

    <div class="grid-stack" id="dashboard-grid">
        @foreach($this->getWidgetsWithGrid() as $widget)
            <div class="grid-stack-item"
                 gs-id="{{ $widget['key'] }}"
                 gs-x="{{ $widget['x'] }}"
                 gs-y="{{ $widget['y'] }}"
                 gs-w="{{ $widget['w'] }}"
                 gs-h="{{ $widget['h'] }}"
                 gs-no-move="true"
                 gs-no-resize="true"
            >
                <div class="grid-stack-item-content">
                    @livewire($widget['class'], [], key('widget-' . $widget['key']))
                </div>
            </div>
        @endforeach
    </div>

    <script src="https://cdn.jsdelivr.net/npm/gridstack@10.3.1/dist/gridstack-all.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            GridStack.init({
                column: 12,
                cellHeight: 65,
                margin: 8,
                animate: false,
                staticGrid: true,
                float: false,
            }, '#dashboard-grid');
        });
    </script>
</x-filament-panels::page>
