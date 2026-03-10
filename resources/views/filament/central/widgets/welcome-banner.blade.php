<div style="background: #1c1410; border: 1px solid rgba(212,146,12,0.12); border-radius: 12px; padding: 1.5rem; position: relative; overflow: hidden;">
    <div style="position: absolute; top: -20px; right: -20px; width: 120px; height: 120px; border-radius: 50%; background: rgba(212,146,12,0.06);"></div>
    <div style="position: absolute; bottom: -25px; right: 60px; width: 90px; height: 90px; border-radius: 50%; background: rgba(232,176,74,0.04);"></div>

    <div style="position: relative; z-index: 1;">
        <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1rem;">
            <span style="font-size: 1.5rem;">🍞</span>
            <div style="color: #ffffff; font-size: 1rem; font-weight: 700;">KneadIt Platform</div>
        </div>
        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem;">
            <div>
                <div style="color: #d4920c; font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.1em; font-weight: 600;">Version</div>
                <div style="color: #ffffff; font-weight: 700; font-size: 0.85rem; margin-top: 0.125rem;">1.0.0</div>
            </div>
            <div>
                <div style="color: #d4920c; font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.1em; font-weight: 600;">Environment</div>
                <div style="color: #ffffff; font-weight: 700; font-size: 0.85rem; margin-top: 0.125rem;">{{ app()->environment() }}</div>
            </div>
            <div>
                <div style="color: #d4920c; font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.1em; font-weight: 600;">PHP</div>
                <div style="color: #ffffff; font-weight: 700; font-size: 0.85rem; margin-top: 0.125rem;">{{ PHP_VERSION }}</div>
            </div>
            <div>
                <div style="color: #d4920c; font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.1em; font-weight: 600;">Laravel</div>
                <div style="color: #ffffff; font-weight: 700; font-size: 0.85rem; margin-top: 0.125rem;">{{ app()->version() }}</div>
            </div>
        </div>
    </div>
</div>
