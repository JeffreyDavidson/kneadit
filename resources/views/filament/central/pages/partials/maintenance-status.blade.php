<div style="margin-bottom: 1.5rem;">
    @if($active)
        <div style="display: inline-flex; align-items: center; gap: 0.75rem; border-radius: 12px; background: rgba(239,68,68,0.12); border: 1px solid rgba(239,68,68,0.25); padding: 1rem 1.5rem;">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="#ef4444" style="width: 28px; height: 28px;">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/>
            </svg>
            <span style="color: #ef4444; font-size: 1.25rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em;">Maintenance</span>
        </div>
    @else
        <div style="display: inline-flex; align-items: center; gap: 0.75rem; border-radius: 12px; background: rgba(16,185,129,0.12); border: 1px solid rgba(16,185,129,0.25); padding: 1rem 1.5rem;">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="#10b981" style="width: 28px; height: 28px;">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
            </svg>
            <span style="color: #10b981; font-size: 1.25rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em;">Online</span>
        </div>
    @endif
</div>
