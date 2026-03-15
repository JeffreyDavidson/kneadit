<x-filament-widgets::widget>
    <div style="background: linear-gradient(135deg, #8B5E3C 0%, #A0724E 50%, #D4A574 100%); border-radius: 16px; padding: 28px 32px 36px; color: white; position: relative; overflow: hidden; box-shadow: 0 4px 24px rgba(139, 94, 60, 0.3);">
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
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" style="width: 16px; height: 16px;"><path fill-rule="evenodd" d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25ZM12.75 9a.75.75 0 0 0-1.5 0v2.25H9a.75.75 0 0 0 0 1.5h2.25V15a.75.75 0 0 0 1.5 0v-2.25H15a.75.75 0 0 0 0-1.5h-2.25V9Z" clip-rule="evenodd" /></svg> New Order
            </a>
            <a href="{{ route('filament.admin.resources.orders.index') }}" style="background: rgba(255,255,255,0.22); color: white; text-decoration: none; padding: 8px 18px; border-radius: 8px; font-size: 0.85rem; font-weight: 600; transition: background 0.2s; display: inline-flex; align-items: center; gap: 6px;">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" style="width: 16px; height: 16px;"><path fill-rule="evenodd" d="M5.625 1.5c-1.036 0-1.875.84-1.875 1.875v17.25c0 1.035.84 1.875 1.875 1.875h12.75c1.035 0 1.875-.84 1.875-1.875V12.75A3.75 3.75 0 0 0 16.5 9h-1.875a1.875 1.875 0 0 1-1.875-1.875V5.25A3.75 3.75 0 0 0 9 1.5H5.625ZM7.5 15a.75.75 0 0 1 .75-.75h7.5a.75.75 0 0 1 0 1.5h-7.5A.75.75 0 0 1 7.5 15Zm.75 2.25a.75.75 0 0 0 0 1.5H12a.75.75 0 0 0 0-1.5H8.25Z" clip-rule="evenodd" /></svg> View Orders
            </a>
            <a href="{{ route('filament.admin.resources.contact-messages.index') }}" style="background: rgba(255,255,255,0.22); color: white; text-decoration: none; padding: 8px 18px; border-radius: 8px; font-size: 0.85rem; font-weight: 600; transition: background 0.2s; display: inline-flex; align-items: center; gap: 6px;">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" style="width: 16px; height: 16px;"><path d="M1.5 8.67v8.58a3 3 0 0 0 3 3h15a3 3 0 0 0 3-3V8.67l-8.928 5.493a3 3 0 0 1-3.144 0L1.5 8.67Z" /><path d="M22.5 6.908V6.75a3 3 0 0 0-3-3h-15a3 3 0 0 0-3 3v.158l9.714 5.978a1.5 1.5 0 0 0 1.572 0L22.5 6.908Z" /></svg> Messages
            </a>
        </div>
    </div>
</x-filament-widgets::widget>
