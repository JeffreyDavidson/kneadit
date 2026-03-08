<x-filament-widgets::widget>
    <div style="background: linear-gradient(135deg, #8B5E3C 0%, #D4A574 50%, #F5E6D3 100%); border-radius: 16px; padding: 28px 32px; color: white; position: relative; overflow: hidden; box-shadow: 0 4px 24px rgba(139, 94, 60, 0.3);">
        {{-- Decorative elements --}}
        <div style="position: absolute; top: -20px; right: -20px; width: 120px; height: 120px; background: rgba(255,255,255,0.08); border-radius: 50%;"></div>
        <div style="position: absolute; bottom: -30px; right: 60px; width: 80px; height: 80px; background: rgba(255,255,255,0.05); border-radius: 50%;"></div>

        <div style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: flex-start; gap: 20px; position: relative; z-index: 1;">
            {{-- Left: Greeting --}}
            <div>
                <h2 style="font-size: 1.65rem; font-weight: 700; margin: 0 0 4px 0; text-shadow: 0 1px 2px rgba(0,0,0,0.15);">
                    {{ $this->getGreeting() }}
                </h2>
                <p style="margin: 0; opacity: 0.85; font-size: 0.95rem;">{{ $this->getTodayDate() }}</p>
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
