<x-filament-widgets::widget>
    <x-admin.dashboard.preview-card heading="Quick Actions">
        <div class="flex flex-wrap gap-2 mt-1">
            @if (\Illuminate\Support\Facades\Route::has('filament.admin.pages.quick-order'))
                <x-admin.btn variant="ghost" :href="route('filament.admin.pages.quick-order')" icon="">
                    <x-heroicon-s-plus-circle class="w-4 h-4" />
                    New Order
                </x-admin.btn>
            @endif
            @if (\Illuminate\Support\Facades\Route::has('filament.admin.resources.orders.index'))
                <x-admin.btn variant="ghost" :href="route('filament.admin.resources.orders.index')" icon="">
                    <x-heroicon-s-document-text class="w-4 h-4" />
                    View Orders
                </x-admin.btn>
            @endif
            @if (\Illuminate\Support\Facades\Route::has('filament.admin.resources.contact-messages.index'))
                <x-admin.btn variant="ghost" :href="route('filament.admin.resources.contact-messages.index')" icon="">
                    <x-heroicon-s-envelope class="w-4 h-4" />
                    Messages
                </x-admin.btn>
            @endif
        </div>
    </x-admin.dashboard.preview-card>
</x-filament-widgets::widget>
