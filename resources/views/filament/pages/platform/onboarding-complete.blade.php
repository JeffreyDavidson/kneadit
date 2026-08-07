<div class="px-4 py-12 text-center">
    <div class="from-brand-300 to-brand-700 mb-6 inline-flex h-[72px] w-[72px] items-center justify-center rounded-full bg-gradient-to-br">
        <x-heroicon-o-check class="h-9 w-9 text-white" stroke-width="2" />
    </div>

    <h2 class="text-brand-900 mb-2 text-3xl font-bold">You're all set!</h2>
    <p class="text-brand-700 mb-10 text-[1.1rem]">Your bakery is ready to go. Here's what you can do next:</p>

    <div class="mx-auto flex max-w-[500px] justify-center gap-4">
        @foreach ([
            ['href' => url('/admin'), 'icon' => 'heroicon-o-home', 'label' => 'Dashboard'],
            ['href' => url('/admin/products'), 'icon' => 'heroicon-o-plus-circle', 'label' => 'Add Products'],
            ['href' => url('/admin/manage-settings'), 'icon' => 'heroicon-o-cog-6-tooth', 'label' => 'Settings'],
        ] as $action)
            <a
                href="{{ $action['href'] }}"
                class="border-brand-200 bg-brand-50 hover:border-brand-300 flex flex-1 flex-col items-center rounded-xl border px-4 py-5 no-underline transition-all"
            >
                <x-dynamic-component
                    :component="$action['icon']"
                    class="text-brand-700 mb-2 h-7 w-7"
                    stroke-width="1.5"
                />
                <span class="text-brand-900 text-sm font-semibold">{{ $action['label'] }}</span>
            </a>
        @endforeach
    </div>
</div>
