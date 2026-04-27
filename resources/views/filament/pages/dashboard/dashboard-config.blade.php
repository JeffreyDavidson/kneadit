<x-filament-panels::page>
    <style @cspnonce>
        .config-layout { display: grid; grid-template-columns: 340px 1fr; gap: 28px; min-height: 600px; }
        @media (max-width: 1024px) { .config-layout { grid-template-columns: 1fr; } }

        /* Left panel — widget list */
        .widget-panel { background: #fdf8f2; border: 1px solid rgba(212,165,116,0.2); border-radius: 16px; padding: 20px; }
        .widget-panel-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; padding-bottom: 12px; border-bottom: 1px solid rgba(212,165,116,0.15); }
        .widget-panel-title { font-size: 0.85rem; font-weight: 700; color: #3d2314; text-transform: uppercase; letter-spacing: 0.5px; margin: 0; }

        .widget-list { display: flex; flex-direction: column; gap: 4px; }
        .widget-card {
            display: flex; align-items: center; gap: 10px;
            padding: 10px 12px; border-radius: 10px;
            border: 1px solid transparent; background: white;
            cursor: grab; user-select: none;
            transition: all 0.15s;
        }
        .widget-card:hover { border-color: rgba(212,165,116,0.3); box-shadow: 0 1px 4px rgba(61,35,20,0.06); }
        .widget-card:active { cursor: grabbing; }
        .widget-card.disabled { opacity: 0.4; background: #f8f8f8; }
        .widget-card.sortable-ghost { opacity: 0.2; }
        .widget-card.sortable-drag { box-shadow: 0 6px 20px rgba(61,35,20,0.12); z-index: 10; }

        .widget-drag { color: #d4c4b0; flex-shrink: 0; }
        .widget-icon { font-size: 1.1rem; flex-shrink: 0; }
        .widget-info { flex: 1; min-width: 0; }
        .widget-name { font-weight: 600; color: #3d2314; margin: 0; font-size: 0.85rem; line-height: 1.2; }
        .widget-desc { font-size: 0.7rem; color: #a08060; margin: 1px 0 0; }

        .span-selector { display: flex; gap: 1px; flex-shrink: 0; background: rgba(212,165,116,0.15); border-radius: 6px; overflow: hidden; }
        .span-btn {
            width: 24px; height: 24px; border: none;
            background: white; color: #a08060; font-size: 0.7rem; font-weight: 700;
            cursor: pointer; transition: all 0.15s; display: flex; align-items: center; justify-content: center;
        }
        .span-btn:hover { background: #fdf8f2; color: #6b4c3b; }
        .span-btn.active { background: #d4a574; color: white; }

        .toggle-mini {
            width: 36px; height: 20px; border-radius: 10px; border: none;
            cursor: pointer; transition: background 0.2s; flex-shrink: 0; position: relative;
        }
        .toggle-mini.on { background: #d4a574; }
        .toggle-mini.off { background: #d0d0d0; }
        .toggle-mini span {
            display: block; width: 16px; height: 16px; border-radius: 50%;
            background: white; box-shadow: 0 1px 2px rgba(0,0,0,0.1);
            position: absolute; top: 2px; transition: left 0.15s;
        }
        .toggle-mini.on span { left: 18px; }
        .toggle-mini.off span { left: 2px; }

        /* Right panel — preview */
        .preview-panel { background: #f5f0ea; border: 1px solid rgba(212,165,116,0.15); border-radius: 16px; padding: 20px; overflow: hidden; }
        .preview-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; padding-bottom: 12px; border-bottom: 1px solid rgba(212,165,116,0.12); }
        .preview-title { font-size: 0.85rem; font-weight: 700; color: #3d2314; text-transform: uppercase; letter-spacing: 0.5px; margin: 0; }
        .preview-badge { font-size: 0.65rem; background: rgba(212,165,116,0.2); color: #8b6844; padding: 3px 8px; border-radius: 6px; font-weight: 600; }

        .preview-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; }

        .preview-widget {
            border-radius: 10px; overflow: hidden;
            border: 1px solid rgba(212,165,116,0.15);
            background: white;
            transition: all 0.2s;
        }
        .preview-widget-header {
            background: linear-gradient(135deg, #6b4c3b, #8b6844);
            padding: 8px 12px;
            display: flex; align-items: center; gap: 6px;
        }
        .preview-widget-header span { color: white; font-size: 0.75rem; font-weight: 600; }
        .preview-widget-header .pw-icon { font-size: 0.85rem; }
        .preview-widget-body { padding: 12px; min-height: 50px; }

        /* Simulated content */
        .pw-stat { display: flex; justify-content: space-between; align-items: baseline; }
        .pw-stat-value { font-size: 1.4rem; font-weight: 700; color: #3d2314; }
        .pw-stat-label { font-size: 0.65rem; color: #a08060; text-transform: uppercase; }
        .pw-bar { height: 6px; border-radius: 3px; background: rgba(212,165,116,0.15); margin-top: 6px; }
        .pw-bar-fill { height: 100%; border-radius: 3px; background: linear-gradient(90deg, #d4a574, #e8b04a); }
        .pw-line { height: 40px; display: flex; align-items: end; gap: 2px; }
        .pw-line-bar { flex: 1; background: rgba(212,165,116,0.3); border-radius: 2px 2px 0 0; min-height: 4px; }
        .pw-row { display: flex; justify-content: space-between; padding: 4px 0; border-bottom: 1px solid rgba(212,165,116,0.08); font-size: 0.7rem; color: #6b4c3b; }
        .pw-row:last-child { border: none; }
        .pw-dot { width: 6px; height: 6px; border-radius: 50%; display: inline-block; margin-right: 4px; }

        .config-footer {
            display: flex; align-items: center; justify-content: space-between;
            padding-top: 20px; margin-top: 24px;
            border-top: 1px solid rgba(212,165,116,0.2);
        }
        .config-footer a {
            font-size: 0.9rem; color: #8b6844; text-decoration: none;
            display: flex; align-items: center; gap: 6px;
        }
        .config-footer a:hover { color: #3d2314; }
    </style>

    <div class="config-layout">
        {{-- Left: Widget List --}}
        <div class="widget-panel">
            <div class="widget-panel-header">
                <p class="widget-panel-title">Widgets</p>
                <x-filament::button size="xs" color="gray" wire:click="resetDefaults">Reset</x-filament::button>
            </div>

            <div class="widget-list" id="widget-sortable">
                @foreach ($widgets as $index => $widget)
                    <div class="widget-card {{ $widget['visible'] ? '' : 'disabled' }}" data-index="{{ $index }}">
                        <div class="widget-drag">
                            <x-heroicon-s-bars-3 class="w-3.5 h-3.5" />
                        </div>
                        <div class="widget-icon">{{ $widget['icon'] }}</div>
                        <div class="widget-info">
                            <p class="widget-name">{{ $widget['name'] }}</p>
                            <p class="widget-desc">{{ $widget['description'] }}</p>
                        </div>
                        <div class="span-selector">
                            @foreach (\App\Enums\Filament\WidgetSize::cases() as $size)
                                <button class="span-btn {{ ($widget['size'] ?? 'sm') === $size->value ? 'active' : '' }}"
                                        wire:click="setSize({{ $index }}, '{{ $size->value }}')" type="button"
                                        title="{{ $size->label() }} ({{ $size->columns() }}/3 width)">{{ strtoupper($size->value) }}</button>
                            @endforeach
                        </div>
                        <button class="toggle-mini {{ $widget['visible'] ? 'on' : 'off' }}"
                                wire:click="toggleWidget({{ $index }})" type="button">
                            <span></span>
                        </button>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Right: Live Preview --}}
        <div class="preview-panel">
            <div class="preview-header">
                <p class="preview-title">Preview</p>
                <span class="preview-badge">Live Preview</span>
            </div>

            <div class="preview-grid">
                @foreach ($widgets as $widget)
                    @if ($widget['visible'])
                        @include('filament.shared.dashboard.widget-card', ['widget' => $widget])
                    @endif
                @endforeach
            </div>
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
        document.addEventListener('DOMContentLoaded', function() {
            const el = document.getElementById('widget-sortable');
            if (!el) return;
            Sortable.create(el, {
                animation: 200,
                ghostClass: 'sortable-ghost',
                dragClass: 'sortable-drag',
                onEnd: function(evt) {
                    if (evt.oldIndex === evt.newIndex) return;
                    @this.call('reorder', evt.oldIndex, evt.newIndex);
                }
            });
        });
    </script>
</x-filament-panels::page>
