<div style="background: #1c1410; border: 1px solid rgba(212,146,12,0.12); border-radius: 12px; padding: 1.5rem; height: 100%; display: flex; flex-direction: column; justify-content: center;">
    <div style="color: #d4920c; font-size: 0.65rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 1rem;">
        Quick Actions
    </div>
    <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
        <a href="{{ \App\Filament\Central\Resources\TenantResource::getUrl('index') }}"
           style="display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.5rem 1rem; background: #d4920c; color: #0c0a09; font-weight: 600; font-size: 0.8rem; border-radius: 8px; text-decoration: none;"
           onmouseover="this.style.background='#e8b04a'" onmouseout="this.style.background='#d4920c'">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" style="width: 14px; height: 14px;">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 0 1 .75-.75h3a.75.75 0 0 1 .75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349M3.75 21V9.349m0 0a3.001 3.001 0 0 0 3.75-.615A2.993 2.993 0 0 0 9.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 0 0 2.25 1.016c.896 0 1.7-.393 2.25-1.015a3.001 3.001 0 0 0 3.75.614m-16.5 0a3.004 3.004 0 0 1-.621-4.72l1.189-1.19A1.5 1.5 0 0 1 5.378 3h13.243a1.5 1.5 0 0 1 1.06.44l1.19 1.189a3 3 0 0 1-.621 4.72M6.75 18h3.75a.75.75 0 0 0 .75-.75V13.5a.75.75 0 0 0-.75-.75H6.75a.75.75 0 0 0-.75.75v3.75c0 .414.336.75.75.75Z"/>
            </svg>
            Bakeries
        </a>
        <a href="{{ \App\Filament\Central\Resources\SupportTicketResource::getUrl('index') }}"
           style="display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.5rem 1rem; background: rgba(212,146,12,0.1); color: #f5d88e; font-weight: 600; font-size: 0.8rem; border: 1px solid rgba(212,146,12,0.2); border-radius: 8px; text-decoration: none;"
           onmouseover="this.style.background='rgba(212,146,12,0.2)'" onmouseout="this.style.background='rgba(212,146,12,0.1)'">
            Support Inbox
        </a>
        <a href="{{ url('/admin/analytics') }}"
           style="display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.5rem 1rem; background: rgba(212,146,12,0.1); color: #f5d88e; font-weight: 600; font-size: 0.8rem; border: 1px solid rgba(212,146,12,0.2); border-radius: 8px; text-decoration: none;"
           onmouseover="this.style.background='rgba(212,146,12,0.2)'" onmouseout="this.style.background='rgba(212,146,12,0.1)'">
            Analytics
        </a>
        <a href="{{ url('/admin/maintenance-mode') }}"
           style="display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.5rem 1rem; background: rgba(212,146,12,0.1); color: #f5d88e; font-weight: 600; font-size: 0.8rem; border: 1px solid rgba(212,146,12,0.2); border-radius: 8px; text-decoration: none;"
           onmouseover="this.style.background='rgba(212,146,12,0.2)'" onmouseout="this.style.background='rgba(212,146,12,0.1)'">
            Maintenance
        </a>
    </div>
</div>
