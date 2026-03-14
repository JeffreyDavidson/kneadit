<x-filament-panels::page>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/gridstack@10.3.1/dist/gridstack.min.css">

    <style>
        .grid-stack { min-height: 200px; }
        .grid-stack-item-content { border-radius: 12px; overflow: hidden !important; }
        .grid-stack-item-content > div { height: 100%; }
        /* Remove grab cursor — dashboard is static */
        .grid-stack-item-content { cursor: default !important; }
        .grid-stack-item { cursor: default !important; }
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
                cellHeight: 60,
                margin: 6,
                animate: false,
                staticGrid: true,  // No drag or resize on dashboard
                float: false,
            }, '#dashboard-grid');
        });
    </script>
</x-filament-panels::page>
