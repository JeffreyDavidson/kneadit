<div style="background: #1c1410; border: 1px solid rgba(212,146,12,0.12); border-radius: 12px; padding: 1.5rem; height: 100%; display: flex; flex-direction: column; justify-content: center;">
    <div style="color: #d4920c; font-size: 0.65rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 1rem;">
        Quick Actions
    </div>
    <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
        <a href="{{ \App\Filament\Central\Resources\TenantResource::getUrl('create') }}"
           style="display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.5rem 1rem; background: #d4920c; color: #0c0a09; font-weight: 600; font-size: 0.8rem; border-radius: 8px; text-decoration: none;"
           onmouseover="this.style.background='#e8b04a'" onmouseout="this.style.background='#d4920c'">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" style="width: 14px; height: 14px;">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
            </svg>
            New Bakery
        </a>
        <a href="{{ \App\Filament\Central\Resources\TenantResource::getUrl('index') }}"
           style="display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.5rem 1rem; background: rgba(212,146,12,0.1); color: #f5d88e; font-weight: 600; font-size: 0.8rem; border: 1px solid rgba(212,146,12,0.2); border-radius: 8px; text-decoration: none;"
           onmouseover="this.style.background='rgba(212,146,12,0.2)'" onmouseout="this.style.background='rgba(212,146,12,0.1)'">
            View All Bakeries
        </a>
        <a href="#"
           style="display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.5rem 1rem; background: rgba(212,146,12,0.1); color: #f5d88e; font-weight: 600; font-size: 0.8rem; border: 1px solid rgba(212,146,12,0.2); border-radius: 8px; text-decoration: none;"
           onmouseover="this.style.background='rgba(212,146,12,0.2)'" onmouseout="this.style.background='rgba(212,146,12,0.1)'">
            Platform Settings
        </a>
    </div>
</div>
