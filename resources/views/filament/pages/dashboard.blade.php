<x-filament-panels::page>
    @php
        $gridConfig = $this->getGridConfig();
    @endphp

    <div class="grid-stack" id="dashboard-grid">
        @foreach($this->getWidgetsWithGrid() as $widget)
            <div class="grid-stack-item"
                 gs-id="{{ $widget['key'] }}"
                 gs-x="{{ $widget['x'] }}"
                 gs-y="{{ $widget['y'] }}"
                 gs-w="{{ $widget['w'] }}"
                 gs-h="{{ $widget['h'] }}"
                 gs-min-w="{{ $widget['minW'] ?? 1 }}"
                 gs-min-h="{{ $widget['minH'] ?? 1 }}"
            >
                <div class="grid-stack-item-content" style="overflow: auto;">
                    @livewire($widget['class'], [], key('widget-' . $widget['key']))
                </div>
            </div>
        @endforeach
    </div>

    {{-- GridStack CSS & JS --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/gridstack@10.3.1/dist/gridstack.min.css">
    <style>
        .grid-stack {
            min-height: 200px;
        }
        .grid-stack-item-content {
            border-radius: 12px;
            overflow: hidden !important;
        }
        /* Make widget content fill the grid cell */
        .grid-stack-item-content > div,
        .grid-stack-item-content .fi-wi {
            height: 100%;
        }
        /* Subtle resize handle */
        .grid-stack-item > .ui-resizable-se,
        .grid-stack-item > .ui-resizable-handle {
            background: none !important;
        }
        .grid-stack-item:hover {
            outline: 2px dashed rgba(212,165,116,0.3);
            outline-offset: -2px;
            border-radius: 12px;
        }
        .grid-stack-placeholder > .placeholder-content {
            border: 2px dashed var(--brand-300, #d4a574) !important;
            border-radius: 12px !important;
            background: rgba(212,165,116,0.05) !important;
        }
        /* Lock button */
        #grid-lock-btn {
            position: fixed;
            bottom: 24px;
            right: 24px;
            z-index: 50;
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            border-radius: 10px;
            border: 1px solid rgba(212,165,116,0.3);
            background: var(--brand-900, #3d2314);
            color: white;
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 4px 16px rgba(61,35,20,0.2);
            transition: all 0.2s;
        }
        #grid-lock-btn:hover {
            background: var(--brand-800, #4a3225);
        }
        #grid-lock-btn.locked {
            background: var(--brand-50, #fdf8f2);
            color: var(--brand-700, #6b4c3b);
            border-color: rgba(212,165,116,0.3);
        }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/gridstack@10.3.1/dist/gridstack-all.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const grid = GridStack.init({
                column: 12,
                cellHeight: 80,
                margin: 8,
                animate: true,
                float: false,
                removable: false,
                resizable: { handles: 'se' },
            });

            // Save on change
            grid.on('change', function(event, items) {
                const layout = {};
                grid.getGridItems().forEach(function(el) {
                    const node = el.gridstackNode;
                    if (node) {
                        layout[node.id] = {
                            x: node.x, y: node.y,
                            w: node.w, h: node.h
                        };
                    }
                });
                @this.call('saveGridLayout', layout);
            });

            // Lock/unlock toggle
            let isLocked = false;
            const lockBtn = document.getElementById('grid-lock-btn');
            if (lockBtn) {
                lockBtn.addEventListener('click', function() {
                    isLocked = !isLocked;
                    grid.enableMove(!isLocked);
                    grid.enableResize(!isLocked);
                    lockBtn.classList.toggle('locked', isLocked);
                    lockBtn.innerHTML = isLocked
                        ? '<svg xmlns="http://www.w3.org/2000/svg" style="width:16px;height:16px" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 1a4.5 4.5 0 0 0-4.5 4.5V9H5a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-6a2 2 0 0 0-2-2h-.5V5.5A4.5 4.5 0 0 0 10 1Zm3 8V5.5a3 3 0 1 0-6 0V9h6Z" clip-rule="evenodd"/></svg> Locked'
                        : '<svg xmlns="http://www.w3.org/2000/svg" style="width:16px;height:16px" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M14.5 1A4.5 4.5 0 0 0 10 5.5V9H3a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-6a2 2 0 0 0-2-2h-1.5V5.5a3 3 0 1 1 6 0v2.75a.75.75 0 0 0 1.5 0V5.5A4.5 4.5 0 0 0 14.5 1Z" clip-rule="evenodd"/></svg> Editing Layout';
                });
            }
        });
    </script>

    <button id="grid-lock-btn">
        <svg xmlns="http://www.w3.org/2000/svg" style="width:16px;height:16px" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M14.5 1A4.5 4.5 0 0 0 10 5.5V9H3a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-6a2 2 0 0 0-2-2h-1.5V5.5a3 3 0 1 1 6 0v2.75a.75.75 0 0 0 1.5 0V5.5A4.5 4.5 0 0 0 14.5 1Z" clip-rule="evenodd"/></svg>
        Editing Layout
    </button>
</x-filament-panels::page>
