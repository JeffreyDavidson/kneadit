<x-central.card class="h-full flex flex-col justify-center">
    <x-central.eyebrow class="mb-4">Quick Actions</x-central.eyebrow>
    <div class="flex gap-2 flex-wrap">
        <x-central.button :href="\App\Filament\Central\Resources\TenantResource::getUrl('index')" class="gap-1.5">
            <x-heroicon-o-building-storefront class="w-3.5 h-3.5" stroke-width="2.5" />
            Bakeries
        </x-central.button>
        <x-central.button variant="secondary" :href="\App\Filament\Central\Resources\SupportTicketResource::getUrl('index')">Support Inbox</x-central.button>
        <x-central.button variant="secondary" :href="url('/admin/analytics')">Analytics</x-central.button>
        <x-central.button variant="secondary" :href="url('/admin/maintenance-mode')">Maintenance</x-central.button>
    </div>
</x-central.card>
