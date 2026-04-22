<div class="text-center px-4 py-12">
    <div class="w-[72px] h-[72px] rounded-full inline-flex items-center justify-center mb-6 bg-gradient-to-br from-brand-300 to-brand-700">
        <x-heroicon-o-check class="w-9 h-9 text-white" stroke-width="2" />
    </div>

    <h2 class="text-3xl font-bold text-brand-900 mb-2">You're all set!</h2>
    <p class="text-[1.1rem] text-brand-700 mb-10">Your bakery is ready to go. Here's what you can do next:</p>

    <div class="flex gap-4 justify-center max-w-[500px] mx-auto">
        @foreach ([
            ['href' => url('/admin'), 'icon' => 'heroicon-o-home', 'label' => 'Dashboard'],
            ['href' => url('/admin/products'), 'icon' => 'heroicon-o-plus-circle', 'label' => 'Add Products'],
            ['href' => url('/admin/manage-settings'), 'icon' => 'heroicon-o-cog-6-tooth', 'label' => 'Settings'],
        ] as $action)
            <a href="{{ $action['href'] }}" class="flex-1 flex flex-col items-center px-4 py-5 rounded-xl border border-brand-200 bg-brand-50 no-underline transition-all hover:border-brand-300">
                <x-dynamic-component :component="$action['icon']" class="w-7 h-7 mb-2 text-brand-700" stroke-width="1.5" />
                <span class="text-sm font-semibold text-brand-900">{{ $action['label'] }}</span>
            </a>
        @endforeach
    </div>
</div>
