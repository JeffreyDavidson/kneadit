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
