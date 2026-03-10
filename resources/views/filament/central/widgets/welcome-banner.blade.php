<div style="
    background: linear-gradient(135deg, #111127 0%, #1a1a3e 50%, #0f172a 100%);
    border: 1px solid rgba(99, 102, 241, 0.15);
    border-radius: 16px;
    padding: 2.5rem 3rem;
    position: relative;
    overflow: hidden;
">
    {{-- Decorative elements --}}
    <div style="position: absolute; top: -30px; right: -30px; width: 200px; height: 200px; border-radius: 50%; background: rgba(99, 102, 241, 0.06);"></div>
    <div style="position: absolute; bottom: -40px; right: 80px; width: 150px; height: 150px; border-radius: 50%; background: rgba(245, 158, 11, 0.04);"></div>

    <div style="position: relative; z-index: 10;">
        <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1rem;">
            <span style="font-size: 2rem;">🍞</span>
            <div>
                <h2 style="color: white; font-size: 1.5rem; font-weight: 700; margin: 0; line-height: 1.2;">
                    KneadIt Platform
                </h2>
                <p style="color: #818cf8; font-size: 0.8rem; font-weight: 500; letter-spacing: 0.05em; text-transform: uppercase; margin: 0;">
                    Command Center
                </p>
            </div>
        </div>
        <p style="color: #a5b4fc; font-size: 0.95rem; margin: 0; max-width: 500px; line-height: 1.6;">
            Manage your bakery tenants, monitor subscriptions, and keep the platform running smoothly.
        </p>

        <div style="display: flex; gap: 2rem; margin-top: 1.5rem;">
            <div>
                <span style="color: #6366f1; font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.1em; font-weight: 600;">Version</span>
                <p style="color: white; font-weight: 600; margin: 0.25rem 0 0;">1.0.0</p>
            </div>
            <div>
                <span style="color: #6366f1; font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.1em; font-weight: 600;">Environment</span>
                <p style="color: white; font-weight: 600; margin: 0.25rem 0 0;">{{ app()->environment() }}</p>
            </div>
            <div>
                <span style="color: #6366f1; font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.1em; font-weight: 600;">PHP</span>
                <p style="color: white; font-weight: 600; margin: 0.25rem 0 0;">{{ PHP_VERSION }}</p>
            </div>
            <div>
                <span style="color: #6366f1; font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.1em; font-weight: 600;">Laravel</span>
                <p style="color: white; font-weight: 600; margin: 0.25rem 0 0;">{{ app()->version() }}</p>
            </div>
        </div>
    </div>
</div>
