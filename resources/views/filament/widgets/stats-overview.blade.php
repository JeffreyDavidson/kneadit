@php
    $cards = $this->getCards();
    $isXl = $this->isSize('xl');
    $sparklineHeight = $isXl ? 48 : 36;
@endphp

<div class="dashboard-stats-grid col-span-full">
    @foreach ($cards as $card)
        <x-admin.dashboard.stat-card
            :label="$card['label']"
            :value="$card['value']"
            :description="$card['delta']"
            :description-color="$card['tone']"
            :chart="$card['chart']"
            :chart-height="$sparklineHeight"
        />
    @endforeach
</div>
