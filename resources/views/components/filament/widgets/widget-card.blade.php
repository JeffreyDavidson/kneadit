@props(['widget', 'configMode' => false, 'index' => null])

@php
    $sizeEnum = \App\Enums\Filament\WidgetSize::tryFrom($widget['size'] ?? '') ?? \App\Enums\Filament\WidgetSize::Small;
    $columns = $sizeEnum->columns();
    $rows = $sizeEnum->rows();
    $isHidden = $configMode && ! ($widget['visible'] ?? true);
    $isXl = $sizeEnum === \App\Enums\Filament\WidgetSize::ExtraLarge;
@endphp

<div
    @class([
        'preview-widget',
        'config-tile' => $configMode,
        'is-hidden' => $isHidden,
        'preview-widget-xl' => $isXl,
    ])
    style="grid-column: span {{ $columns }}; grid-row: span {{ $rows }};"
    @if ($configMode) data-index="{{ $index }}" @endif
>
    @if ($configMode)
        @php
            $allowedSizes = \App\Filament\Shared\Dashboard\WidgetMeta::allowedSizesFor($widget['key']);
        @endphp

        <div class="config-controls">
            <button type="button" class="config-ctrl config-drag" title="Drag to reorder">
                <x-heroicon-s-bars-3 class="h-4 w-4" />
            </button>

            @if (count($allowedSizes) > 1)
                <div class="config-size-group">
                    @foreach ($allowedSizes as $size)
                        <button
                            type="button"
                            @class(['config-size-btn', 'active' => ($widget['size'] ?? 'sm') === $size->value])
                            wire:click="setSize({{ $index }}, '{{ $size->value }}')"
                            title="{{ $size->getLabel() }} ({{ $size->columns() }}/3 width)"
                        >
                            {{ strtoupper($size->value) }}
                        </button>
                    @endforeach
                </div>
            @else
                <span
                    class="config-size-locked"
                    title="This widget is fixed at {{ $allowedSizes[0]->getLabel() }}"
                >{{ strtoupper($allowedSizes[0]->value) }}</span>
            @endif

            <button
                type="button"
                @class(['config-ctrl config-toggle', 'is-on' => $widget['visible'] ?? true, 'is-off' => ! ($widget['visible'] ?? true)])
                wire:click="toggleWidget({{ $index }})"
                title="{{ ($widget['visible'] ?? true) ? 'Hide widget' : 'Show widget' }}"
            >
                @if ($widget['visible'] ?? true)
                    <x-heroicon-s-eye class="h-4 w-4" />
                @else
                    <x-heroicon-s-eye-slash class="h-4 w-4" />
                @endif
            </button>
        </div>
    @endif

    <div class="preview-widget-header">
        @if ($widget['icon'] ?? null)
            <x-filament::icon :icon="$widget['icon']" class="pw-icon" />
        @endif
        <span>{{ $widget['name'] }}</span>
    </div>

    <x-filament.widgets.widget-preview :widget="$widget" />
</div>
