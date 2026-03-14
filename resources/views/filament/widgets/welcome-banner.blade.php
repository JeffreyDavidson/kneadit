<x-filament-widgets::widget>
    <div style="background: linear-gradient(135deg, #8B5E3C 0%, #A0724E 50%, #D4A574 100%); border-radius: 16px; padding: 28px 32px; color: white; position: relative; overflow: hidden; box-shadow: 0 4px 24px rgba(139, 94, 60, 0.3);">
        {{-- Decorative elements --}}
        <div style="position: absolute; top: -20px; right: -20px; width: 120px; height: 120px; background: rgba(255,255,255,0.08); border-radius: 50%;"></div>
        <div style="position: absolute; bottom: -30px; right: 60px; width: 80px; height: 80px; background: rgba(255,255,255,0.05); border-radius: 50%;"></div>

        <div style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: flex-start; gap: 20px; position: relative; z-index: 1;">
            {{-- Left: Greeting --}}
            <div style="display: flex; align-items: center; gap: 12px;">
                <div>
                    <h2 style="font-size: 1.65rem; font-weight: 700; margin: 0 0 4px 0; text-shadow: 0 1px 2px rgba(0,0,0,0.15);">
                        {{ $this->getGreeting() }}
                    </h2>
                    <p style="margin: 0; opacity: 0.85; font-size: 0.95rem;">{{ $this->getTodayDate() }}</p>
                </div>
                <a href="{{ route('filament.admin.pages.dashboard-config') }}" style="opacity: 0.5; color: white; margin-left: 4px;" title="Customize Dashboard">
                    <svg xmlns="http://www.w3.org/2000/svg" style="width: 16px; height: 16px;" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd" />
                    </svg>
                </a>
            </div>

            {{-- Right: Quick stats --}}
            <div style="display: flex; gap: 16px; flex-wrap: wrap;">
                <div style="background: rgba(255,255,255,0.18); backdrop-filter: blur(10px); border-radius: 12px; padding: 12px 20px; text-align: center; min-width: 100px;">
                    <div style="font-size: 1.5rem; font-weight: 700;">{{ $this->getOrdersToday() }}</div>
                    <div style="font-size: 0.75rem; opacity: 0.85; text-transform: uppercase; letter-spacing: 0.5px;">Orders Today</div>
                </div>
                <div style="background: rgba(255,255,255,0.18); backdrop-filter: blur(10px); border-radius: 12px; padding: 12px 20px; text-align: center; min-width: 100px;">
                    <div style="font-size: 1.5rem; font-weight: 700;">${{ $this->getRevenueToday() }}</div>
                    <div style="font-size: 0.75rem; opacity: 0.85; text-transform: uppercase; letter-spacing: 0.5px;">Revenue Today</div>
                </div>
                <div style="background: rgba(255,255,255,0.18); backdrop-filter: blur(10px); border-radius: 12px; padding: 12px 20px; text-align: center; min-width: 100px;">
                    <div style="font-size: 1.5rem; font-weight: 700; {{ $this->getPendingOrders() > 0 ? 'color: #FFD700;' : '' }}">{{ $this->getPendingOrders() }}</div>
                    <div style="font-size: 0.75rem; opacity: 0.85; text-transform: uppercase; letter-spacing: 0.5px;">Pending</div>
                </div>
            </div>
        </div>

        {{-- Quick actions --}}
        <div style="display: flex; gap: 10px; margin-top: 20px; flex-wrap: wrap; position: relative; z-index: 1;">
            <a href="{{ route('filament.admin.resources.orders.create') }}" style="background: rgba(255,255,255,0.22); color: white; text-decoration: none; padding: 8px 18px; border-radius: 8px; font-size: 0.85rem; font-weight: 600; transition: background 0.2s; display: inline-flex; align-items: center; gap: 6px;">
                <x-heroicon-s-plus-circle style="width: 16px; height: 16px;" /> New Order
            </a>
            <a href="{{ route('filament.admin.resources.orders.index') }}" style="background: rgba(255,255,255,0.22); color: white; text-decoration: none; padding: 8px 18px; border-radius: 8px; font-size: 0.85rem; font-weight: 600; transition: background 0.2s; display: inline-flex; align-items: center; gap: 6px;">
                <x-heroicon-s-clipboard-document-list style="width: 16px; height: 16px;" /> View Orders
            </a>
            <a href="{{ route('filament.admin.resources.contact-messages.index') }}" style="background: rgba(255,255,255,0.22); color: white; text-decoration: none; padding: 8px 18px; border-radius: 8px; font-size: 0.85rem; font-weight: 600; transition: background 0.2s; display: inline-flex; align-items: center; gap: 6px;">
                <x-heroicon-s-envelope style="width: 16px; height: 16px;" /> Messages
            </a>
        </div>
    </div>
</x-filament-widgets::widget>
