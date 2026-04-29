@props([
    'label',
    'value',
    'description' => null,
    'descriptionColor' => null,
    'icon' => null,
    'chart' => [],
    'chartHeight' => 36,
])

<div {{ $attributes->class('stat-card') }}>
    <div class="stat-card-content">
        <div class="stat-card-label">
            @if ($icon)
                <span class="stat-card-icon">{{ $icon }}</span>
            @endif
            <span>{{ $label }}</span>
        </div>

        <div class="stat-card-value">{{ $value }}</div>

        @if ($description)
            <div class="stat-card-description" @if ($descriptionColor) style="color: {{ $descriptionColor }};" @endif>
                {{ $description }}
            </div>
        @endif
    </div>

    @if (! empty($chart))
        <x-admin.dashboard.spark-line
            :bars="$chart"
            :height="$chartHeight"
            :color="$descriptionColor ?? 'var(--pw-card-accent)'"
            class="stat-card-chart"
        />
    @endif
</div>
