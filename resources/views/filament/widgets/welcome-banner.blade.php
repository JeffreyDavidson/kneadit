<x-filament-widgets::widget>
    <div class="dashboard-action-panel">
        <div>
            <div class="dashboard-action-panel__eyebrow">Quick actions</div>
            <h2 class="dashboard-action-panel__title">Keep the next order moving</h2>
            <p class="dashboard-action-panel__copy">
                Create an order, triage the queue, or answer customer messages from one steady place.
            </p>
        </div>

        <div class="dashboard-action-panel__actions">
            @if (\Illuminate\Support\Facades\Route::has('filament.admin.pages.quick-order'))
                <a
                    href="{{ route('filament.admin.pages.quick-order') }}"
                    class="dashboard-action-button dashboard-action-button--primary"
                >
                    <x-heroicon-s-plus-circle class="h-4 w-4" stroke-width="2.5" />
                    New Order
                </a>
            @endif
            @if (\Illuminate\Support\Facades\Route::has('filament.admin.resources.orders.index'))
                <a href="{{ route('filament.admin.resources.orders.index') }}" class="dashboard-action-button">
                    <x-heroicon-o-clipboard-document-list class="h-4 w-4" />
                    Orders
                </a>
            @endif
            @if (\Illuminate\Support\Facades\Route::has('filament.admin.resources.contact-messages.index'))
                <a
                    href="{{ route('filament.admin.resources.contact-messages.index') }}"
                    class="dashboard-action-button"
                >
                    <x-heroicon-o-inbox class="h-4 w-4" />
                    Messages
                </a>
            @endif
        </div>
    </div>
</x-filament-widgets::widget>
