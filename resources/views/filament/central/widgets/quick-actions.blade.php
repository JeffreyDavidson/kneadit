<div style="
    background: linear-gradient(135deg, #1c1410 0%, #2a1f18 100%);
    border: 1px solid rgba(212, 146, 12, 0.15);
    border-radius: 12px;
    padding: 1rem 1.5rem;
    height: 100%;
    display: flex;
    flex-direction: column;
    justify-content: center;
">
    <h3 style="color: #f5d88e; font-size: 0.7rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.1em; margin: 0 0 0.75rem;">
        Quick Actions
    </h3>
    <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
        <a href="{{ \App\Filament\Central\Resources\TenantResource::getUrl('create') }}"
           style="display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.5rem 1rem; background: #d4920c; color: #1c1410; font-weight: 600; font-size: 0.8rem; border-radius: 8px; text-decoration: none; transition: background 0.2s;"
           onmouseover="this.style.background='#e8b04a'" onmouseout="this.style.background='#d4920c'">
            <svg xmlns="http://www.w3.org/2000/svg" style="width:14px;height:14px;" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            New Bakery
        </a>
        <a href="{{ \App\Filament\Central\Resources\TenantResource::getUrl('index') }}"
           style="display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.5rem 1rem; background: #3d2c1e; color: #f5d88e; font-weight: 600; font-size: 0.8rem; border: 1px solid rgba(212, 146, 12, 0.3); border-radius: 8px; text-decoration: none; transition: background 0.2s;"
           onmouseover="this.style.background='#4a3525'" onmouseout="this.style.background='#3d2c1e'">
            View All Bakeries
        </a>
        <a href="#"
           style="display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.5rem 1rem; background: #3d2c1e; color: #f5d88e; font-weight: 600; font-size: 0.8rem; border: 1px solid rgba(212, 146, 12, 0.3); border-radius: 8px; text-decoration: none; transition: background 0.2s;"
           onmouseover="this.style.background='#4a3525'" onmouseout="this.style.background='#3d2c1e'">
            Platform Settings
        </a>
    </div>
</div>
