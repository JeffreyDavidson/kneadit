<?php

test('the dashboard settings link is styled by a class, not inline colours or hover handlers', function () {
    $html = view('filament.render-hooks.dashboard-settings-link', ['url' => 'https://example.test/admin/dashboard-config'])->render();

    expect($html)
        ->toContain('href="https://example.test/admin/dashboard-config"')
        ->toContain('Customize Dashboard')
        ->toContain('class="dashboard-settings-link"')
        ->not->toContain('style=')
        ->not->toContain('onmouseover')
        ->not->toContain('onmouseout');
});
