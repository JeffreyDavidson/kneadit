@php $products = $this->getProducts(); @endphp

<x-admin.dashboard.preview-card heading="Top Products This Month" icon="⭐">
    @forelse ($products as $product)
        <x-admin.dashboard.bar-row
            :label="$product['name']"
            :pct="$product['percentage']"
            :value="$product['revenue_formatted'].' · '.$product['units_sold'].' sold'"
        />
    @empty
        <div style="text-align: center; padding: 12px 0; color: var(--pw-card-text-muted); font-size: 0.75rem;">
            No sales yet this month
        </div>
    @endforelse
</x-admin.dashboard.preview-card>
