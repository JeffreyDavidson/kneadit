<div style="margin-bottom: 1rem;">
    @if($active)
        <div style="display: inline-flex; align-items: center; gap: 0.5rem; border-radius: 8px; background: rgba(239,68,68,0.15); padding: 0.75rem 1rem; color: #ef4444;">
            <svg xmlns="http://www.w3.org/2000/svg" style="width:24px;height:24px;" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/></svg>
            <span style="font-size: 1.1rem; font-weight: 700;">MAINTENANCE</span>
        </div>
    @else
        <div style="display: inline-flex; align-items: center; gap: 0.5rem; border-radius: 8px; background: rgba(16,185,129,0.15); padding: 0.75rem 1rem; color: #10b981;">
            <svg xmlns="http://www.w3.org/2000/svg" style="width:24px;height:24px;" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
            <span style="font-size: 1.1rem; font-weight: 700;">ONLINE</span>
        </div>
    @endif
</div>
