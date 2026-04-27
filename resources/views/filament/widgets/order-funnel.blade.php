@php
    $stages = $this->getVisibleStages();
    $hasOrderRoute = \Illuminate\Support\Facades\Route::has('filament.admin.resources.orders.index');
@endphp

<x-admin.dashboard.preview-card heading="Order Pipeline" icon="🔽">
    @if ($this->isSize('md'))
        <x-admin.dashboard.stat-row label="Active orders" :value="array_sum(array_column($stages, 'count'))" class="mb-2" />
    @endif

    @foreach ($stages as $stage)
        @php
            $href = $hasOrderRoute ? route('filament.admin.resources.orders.index', ['tableFilters[status][value]' => $stage['key']]) : null;
        @endphp
        <x-admin.dashboard.list-row :value="$stage['count']" :dot-color="$stage['dotColor'] ?? null">
            @if ($href)
                <a href="{{ $href }}" style="color: var(--pw-card-text); text-decoration: none;">{{ $stage['label'] }}</a>
            @else
                {{ $stage['label'] }}
            @endif
        </x-admin.dashboard.list-row>
    @endforeach
</x-admin.dashboard.preview-card>
