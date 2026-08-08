<x-central.card class="flex h-full flex-col justify-center">
    <x-central.eyebrow class="mb-4">Quick Actions</x-central.eyebrow>
    <div class="flex flex-wrap gap-2">
        <x-central.button :href="\App\Filament\Central\Resources\TenantResource::getUrl('index')" class="gap-1.5">
            <x-heroicon-o-building-storefront class="h-3.5 w-3.5" stroke-width="2.5" />
            Bakeries
        </x-central.button>
        <x-central.button
            variant="secondary"
            size="sm"
            :href="\App\Filament\Central\Resources\SupportTicketResource::getUrl('index')"
        >
            Support Inbox</x-central.button>
        <x-central.button variant="secondary" size="sm" :href="url('/admin/analytics')">Analytics</x-central.button>
        <x-central.button variant="secondary" size="sm" :href="url('/admin/maintenance-mode')">
            Maintenance</x-central.button>
    </div>
</x-central.card>
