@props(['icon' => 'heroicon-o-arrow-trending-up'])

{{-- Preserve Filament's chart DOM contract while reusing the tenant dashboard shell. --}}
@php
    use Filament\Widgets\View\Components\ChartWidgetComponent;
    use Illuminate\View\ComponentAttributeBag;

    $heading = $this->getHeading();
    $type = $this->getType();
    $color = $this->getColor();
    $maxHeight = $this->getMaxHeight();
@endphp

<div class="col-span-full">
    <x-tenant-admin.dashboard.preview-card :heading="$heading" :icon="$icon">
        <div
            @if ($pollingInterval = $this->getPollingInterval())
                wire:poll.{{ $pollingInterval }}="updateChartData"
            @endif
        >
            <div
                x-load
                x-load-src="{{ \Filament\Support\Facades\FilamentAsset::getAlpineComponentSrc('chart', 'filament/widgets') }}"
                wire:ignore
                x-init="knThemeChart($data)"
                data-chart-type="{{ $type }}"
                x-data="chart({
                    cachedData: @js($this->getCachedData()),
                    maxHeight: @js($maxHeight),
                    options: @js($this->getOptions()),
                    type: @js($type),
                })"
                {{
                    (new ComponentAttributeBag)
                        ->color(ChartWidgetComponent::class, $color)
                        ->class([
                            'fi-wi-chart-canvas-ctn',
                            'fi-wi-chart-canvas-ctn-no-aspect-ratio' => filled($maxHeight),
                        ])
                }}
            >
                <canvas x-ref="canvas" @if ($maxHeight) style="max-height: {{ $maxHeight }}" @endif></canvas>

                <span x-ref="backgroundColorElement" class="fi-wi-chart-bg-color"></span>
                <span x-ref="borderColorElement" class="fi-wi-chart-border-color"></span>
                <span x-ref="gridColorElement" class="fi-wi-chart-grid-color"></span>
                <span x-ref="textColorElement" class="fi-wi-chart-text-color"></span>
            </div>
        </div>
    </x-tenant-admin.dashboard.preview-card>
</div>

{{-- Chart.js draws on a canvas and can't read CSS variables. A dataset can name a design-system
     token in `colorToken`; this paints the dataset with that token's value for the theme that is
     showing, and again when the Light / Dark switch changes (the same approach as the central
     Analytics page). The hex colours the widget sets stay as the first paint. --}}
@assets
    <script @cspnonce>
        window.knThemeChart ??= (component) => {
            const token = (name) => getComputedStyle(document.documentElement).getPropertyValue(name).trim();
            const withAlpha = (hex, alpha) =>
                hex +
                Math.round(alpha * 255)
                    .toString(16)
                    .padStart(2, '0');

            const paint = () => {
                const chart = component.getChart?.();

                if (!chart) {
                    return false;
                }

                chart.data.datasets.forEach((dataset) => {
                    const color = dataset.colorToken ? token(dataset.colorToken) : '';

                    if (!color) {
                        return;
                    }

                    const type = dataset.type ?? chart.config.type;

                    dataset.borderColor = color;
                    dataset.pointBackgroundColor = color;
                    dataset.backgroundColor =
                        type === 'bar' ? color : dataset.fill ? withAlpha(color, 0.12) : 'transparent';
                });

                chart.update('resize');

                return true;
            };

            // The chart is created on the next tick after this runs, so wait for it.
            const paintWhenReady = (attempts = 0) => {
                if (!paint() && attempts < 60) {
                    requestAnimationFrame(() => paintWhenReady(attempts + 1));
                }
            };

            const repaint = () => {
                if (!component.$el.isConnected) {
                    window.removeEventListener('theme-changed', repaint);

                    return;
                }

                requestAnimationFrame(paint);
            };

            window.addEventListener('theme-changed', repaint);
            component.$wire?.$on('updateChartData', repaint);
            paintWhenReady();
        };
    </script>
@endassets
