<x-filament-panels::page>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/gridstack@10.3.1/dist/gridstack.min.css">

    <style>
        .grid-stack { min-height: 400px; background: repeating-linear-gradient(90deg, transparent, transparent calc(8.333% - 1px), rgba(212,165,116,0.08) calc(8.333% - 1px), rgba(212,165,116,0.08) 8.333%); border-radius: 12px; }
        .grid-stack-item-content { border-radius: 10px; border: 1px solid rgba(212,165,116,0.25); background: var(--brand-50, #fdf8f2); padding: 14px 18px; cursor: grab; display: flex; align-items: center; gap: 14px; overflow: hidden !important; }
        .grid-stack-item-content:hover { border-color: var(--brand-300, #d4a574); box-shadow: 0 2px 8px rgba(61,35,20,0.08); }
        .grid-stack-item.ui-draggable-dragging .grid-stack-item-content { cursor: grabbing; box-shadow: 0 8px 24px rgba(61,35,20,0.15); border-color: var(--brand-300, #d4a574); }
        .grid-stack-placeholder > .placeholder-content { border: 2px dashed var(--brand-300, #d4a574) !important; border-radius: 10px !important; background: rgba(212,165,116,0.05) !important; }
        .widget-hidden .grid-stack-item-content { opacity: 0.4; background: #f5f5f5; border-style: dashed; }
        .widget-icon { font-size: 1.5rem; flex-shrink: 0; width: 36px; text-align: center; }
        .widget-info { flex: 1; min-width: 0; }
        .widget-name { font-weight: 600; color: var(--brand-900, #3d2314); margin: 0; font-size: 0.95rem; }
        .widget-desc { font-size: 0.8rem; color: var(--brand-500, #8b6844); margin: 2px 0 0 0; }
        .widget-size { font-size: 0.7rem; color: var(--brand-400); font-family: monospace; flex-shrink: 0; }
        .widget-toggle { flex-shrink: 0; }
        .widget-toggle button { position: relative; display: inline-flex; height: 26px; width: 48px; align-items: center; border-radius: 13px; border: none; cursor: pointer; transition: background 0.2s; }
        .widget-toggle button.on { background: var(--brand-300, #d4a574); }
        .widget-toggle button.off { background: #ccc; }
        .widget-toggle button span { display: inline-block; height: 20px; width: 20px; border-radius: 50%; background: white; box-shadow: 0 1px 3px rgba(0,0,0,0.15); transition: transform 0.2s; }
        .widget-toggle button.on span { transform: translateX(24px); }
        .widget-toggle button.off span { transform: translateX(3px); }
        .config-header { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 20px; }
        .config-header p { color: var(--brand-500); font-size: 0.9rem; margin: 0; }
        .config-footer { display: flex; align-items: center; justify-content: space-between; padding-top: 16px; border-top: 1px solid rgba(212,165,116,0.2); margin-top: 20px; }
        .config-footer a { font-size: 0.9rem; color: var(--brand-500); text-decoration: none; display: flex; align-items: center; gap: 6px; }
        .config-footer a:hover { color: var(--brand-700); }
    </style>

    <div class="config-header">
        <p>Drag to rearrange, resize by corners, toggle visibility. Changes auto-save.</p>
        <div style="display: flex; gap: 8px;">
            <x-filament::button color="gray" wire:click="resetDefaults" id="reset-btn">
                Reset to Defaults
            </x-filament::button>
        </div>
    </div>

    <div class="grid-stack" id="config-grid">
        @foreach($widgets as $index => $widget)
            <div class="grid-stack-item {{ $widget['visible'] ? '' : 'widget-hidden' }}"
                 gs-id="{{ $widget['key'] }}"
                 gs-x="{{ $widget['x'] }}"
                 gs-y="{{ $widget['y'] }}"
                 gs-w="{{ $widget['w'] }}"
                 gs-h="{{ $widget['h'] }}"
                 gs-min-w="{{ $widget['minW'] }}"
                 gs-min-h="{{ $widget['minH'] }}"
                 data-key="{{ $widget['key'] }}"
                 data-visible="{{ $widget['visible'] ? '1' : '0' }}"
            >
                <div class="grid-stack-item-content">
                    <div class="widget-icon">{{ $widget['icon'] }}</div>
                    <div class="widget-info">
                        <p class="widget-name">{{ $widget['name'] }}</p>
                        <p class="widget-desc">{{ $widget['description'] }}</p>
                    </div>
                    <div class="widget-size" data-size></div>
                    <div class="widget-toggle">
                        <button class="{{ $widget['visible'] ? 'on' : 'off' }}"
                                onclick="toggleWidget(this, '{{ $widget['key'] }}')"
                                type="button">
                            <span></span>
                        </button>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="config-footer">
        <a href="{{ route('filament.admin.pages.dashboard') }}">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" style="width: 16px; height: 16px;"><path fill-rule="evenodd" d="M11.78 5.22a.75.75 0 0 1 0 1.06L8.06 10l3.72 3.72a.75.75 0 1 1-1.06 1.06l-4.25-4.25a.75.75 0 0 1 0-1.06l4.25-4.25a.75.75 0 0 1 1.06 0Z" clip-rule="evenodd" /></svg>
            Back to Dashboard
        </a>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/gridstack@10.3.1/dist/gridstack-all.js"></script>
    <script>
        let grid;
        let saveTimeout;

        function initGrid() {
            const el = document.getElementById('config-grid');
            if (!el || el.gridstack) return;

            grid = GridStack.init({
                column: 12,
                cellHeight: 60,
                margin: 6,
                animate: true,
                float: false,
                resizable: { handles: 'e,se,s' },
            }, el);

            // Update size labels
            updateSizeLabels();

            // Auto-save on change
            grid.on('change', function() {
                updateSizeLabels();
                debounceSave();
            });
        }

        function updateSizeLabels() {
            if (!grid) return;
            grid.getGridItems().forEach(function(el) {
                const node = el.gridstackNode;
                const label = el.querySelector('[data-size]');
                if (node && label) {
                    label.textContent = node.w + '×' + node.h;
                }
            });
        }

        function toggleWidget(btn, key) {
            const item = btn.closest('.grid-stack-item');
            const isOn = btn.classList.contains('on');

            btn.classList.toggle('on', !isOn);
            btn.classList.toggle('off', isOn);
            item.classList.toggle('widget-hidden', isOn);
            item.dataset.visible = isOn ? '0' : '1';

            @this.call('toggleWidget', key);
            debounceSave();
        }

        function debounceSave() {
            clearTimeout(saveTimeout);
            saveTimeout = setTimeout(saveLayout, 800);
        }

        function saveLayout() {
            if (!grid) return;
            const items = [];
            grid.getGridItems().forEach(function(el) {
                const node = el.gridstackNode;
                if (node) {
                    items.push({
                        id: node.id,
                        x: node.x,
                        y: node.y,
                        w: node.w,
                        h: node.h
                    });
                }
            });
            @this.call('saveLayout', items);
        }

        document.addEventListener('DOMContentLoaded', initGrid);

        // Re-init after Livewire updates (e.g. reset)
        if (typeof Livewire !== 'undefined') {
            Livewire.hook('morph.updated', () => {
                setTimeout(initGrid, 200);
            });
        }

        // Handle reset button - need to reinit grid after Livewire re-renders
        document.getElementById('reset-btn')?.addEventListener('click', function() {
            setTimeout(function() {
                if (grid) { grid.destroy(false); grid = null; }
                initGrid();
            }, 500);
        });
    </script>
</x-filament-panels::page>
