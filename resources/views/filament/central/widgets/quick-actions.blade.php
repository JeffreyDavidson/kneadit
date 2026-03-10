<div style="
    background: linear-gradient(135deg, #1c1410 0%, #2a1f18 100%);
    border: 1px solid rgba(212, 146, 12, 0.15);
    border-radius: 12px;
    padding: 1.25rem 1.5rem;
">
    <h3 style="color: #f5d88e; font-size: 0.85rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.08em; margin: 0 0 1rem;">
        Quick Actions
    </h3>
    <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
        <a href="{{ \App\Filament\Central\Resources\TenantResource::getUrl('create') }}"
           style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.6rem 1.25rem; background: #d4920c; color: #1c1410; font-weight: 600; font-size: 0.85rem; border-radius: 8px; text-decoration: none; transition: background 0.2s;"
           onmouseover="this.style.background='#e8b04a'" onmouseout="this.style.background='#d4920c'">
            <svg xmlns="http://www.w3.org/2000/svg" style="width:16px;height:16px;" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            New Bakery
        </a>
        <a href="{{ \App\Filament\Central\Resources\TenantResource::getUrl('index') }}"
           style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.6rem 1.25rem; background: #3d2c1e; color: #f5d88e; font-weight: 600; font-size: 0.85rem; border: 1px solid rgba(212, 146, 12, 0.3); border-radius: 8px; text-decoration: none; transition: background 0.2s;"
           onmouseover="this.style.background='#4a3525'" onmouseout="this.style.background='#3d2c1e'">
            <svg xmlns="http://www.w3.org/2000/svg" style="width:16px;height:16px;" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0ZM3.75 12h.007v.008H3.75V12Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm-.375 5.25h.007v.008H3.75v-.008Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"/></svg>
            View All Bakeries
        </a>
        <a href="#"
           style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.6rem 1.25rem; background: #3d2c1e; color: #f5d88e; font-weight: 600; font-size: 0.85rem; border: 1px solid rgba(212, 146, 12, 0.3); border-radius: 8px; text-decoration: none; transition: background 0.2s;"
           onmouseover="this.style.background='#4a3525'" onmouseout="this.style.background='#3d2c1e'">
            <svg xmlns="http://www.w3.org/2000/svg" style="width:16px;height:16px;" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
            Platform Settings
        </a>
    </div>
</div>
