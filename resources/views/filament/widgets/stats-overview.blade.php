@php
    $cards = $this->getCards();
    $isXl = $this->isSize('xl');
    $sparklineHeight = $isXl ? 48 : 36;
@endphp

<div class="col-span-full" style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 24px;">
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
