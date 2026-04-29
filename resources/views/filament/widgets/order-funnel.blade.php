@php
    $stages = $this->getVisibleStages();
    $hasOrderRoute = \Illuminate\Support\Facades\Route::has('filament.admin.resources.orders.index');
@endphp

<x-admin.dashboard.preview-card heading="Order Pipeline" icon="🔽" class="md:col-span-2 xl:col-span-2">
    @if ($this->isSize('md'))
        <x-admin.dashboard.stat-row label="Active orders" :value="array_sum(array_column($stages, 'count'))" class="mb-2" />
    @endif

    @foreach ($stages as $stage)
        @php
            $href = $hasOrderRoute ? route('filament.admin.resources.orders.index', ['tableFilters[status][value]' => $stage['key']]) : null;
        @endphp
        <x-admin.dashboard.list-row :dot-color="$stage['dotColor'] ?? null">
            <span style="color: var(--pw-card-text-muted); margin-right: 6px;">{{ $stage['count'] }}</span>
            @if ($href)
                <a href="{{ $href }}" style="color: var(--pw-card-text); text-decoration: none;">{{ $stage['label'] }}</a>
            @else
                <span style="color: var(--pw-card-text);">{{ $stage['label'] }}</span>
            @endif
            <x-slot:value>
                <span style="color: var(--pw-card-text); font-weight: 600;">{{ $stage['total_formatted'] }}</span>
            </x-slot:value>
        </x-admin.dashboard.list-row>
    @endforeach
</x-admin.dashboard.preview-card>
