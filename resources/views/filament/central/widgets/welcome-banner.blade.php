<div style="
    background: linear-gradient(135deg, #1c1410 0%, #2a1f18 50%, #1a1008 100%);
    border: 1px solid rgba(212, 146, 12, 0.15);
    border-radius: 12px;
    padding: 1rem 1.5rem;
    position: relative;
    overflow: hidden;
">
    {{-- Decorative elements --}}
    <div style="position: absolute; top: -20px; right: -20px; width: 120px; height: 120px; border-radius: 50%; background: rgba(212, 146, 12, 0.06);"></div>
    <div style="position: absolute; bottom: -25px; right: 60px; width: 90px; height: 90px; border-radius: 50%; background: rgba(232, 176, 74, 0.04);"></div>

    <div style="position: relative; z-index: 10; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 0.75rem;">
        <div style="display: flex; align-items: center; gap: 0.6rem;">
            <span style="font-size: 1.5rem;">🍞</span>
            <h2 style="color: white; font-size: 1.25rem; font-weight: 700; margin: 0;">
                KneadIt Platform
            </h2>
        </div>

        <div style="display: flex; gap: 1.5rem; align-items: center;">
            <div style="text-align: center;">
                <span style="color: #d4920c; font-size: 0.6rem; text-transform: uppercase; letter-spacing: 0.1em; font-weight: 600;">Version</span>
                <p style="color: white; font-weight: 600; margin: 0.1rem 0 0; font-size: 0.85rem;">1.0.0</p>
            </div>
            <div style="text-align: center;">
                <span style="color: #d4920c; font-size: 0.6rem; text-transform: uppercase; letter-spacing: 0.1em; font-weight: 600;">Environment</span>
                <p style="color: white; font-weight: 600; margin: 0.1rem 0 0; font-size: 0.85rem;">{{ app()->environment() }}</p>
            </div>
            <div style="text-align: center;">
                <span style="color: #d4920c; font-size: 0.6rem; text-transform: uppercase; letter-spacing: 0.1em; font-weight: 600;">PHP</span>
                <p style="color: white; font-weight: 600; margin: 0.1rem 0 0; font-size: 0.85rem;">{{ PHP_VERSION }}</p>
            </div>
            <div style="text-align: center;">
                <span style="color: #d4920c; font-size: 0.6rem; text-transform: uppercase; letter-spacing: 0.1em; font-weight: 600;">Laravel</span>
                <p style="color: white; font-weight: 600; margin: 0.1rem 0 0; font-size: 0.85rem;">{{ app()->version() }}</p>
            </div>
        </div>
    </div>
</div>
