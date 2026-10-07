<div class="kn-onboarding-next">
    @foreach ([
        ['href' => url('/admin'), 'icon' => 'heroicon-o-home', 'label' => 'Dashboard'],
        ['href' => url('/admin/products'), 'icon' => 'heroicon-o-plus-circle', 'label' => 'Add products'],
        ['href' => url('/admin/manage-settings'), 'icon' => 'heroicon-o-cog-6-tooth', 'label' => 'Settings'],
    ] as $action)
        <a href="{{ $action['href'] }}" class="kn-onboarding-next-link">
            <x-dynamic-component :component="$action['icon']" class="kn-onboarding-next-icon" stroke-width="1.5" />
            <span>{{ $action['label'] }}</span>
        </a>
    @endforeach
</div>
