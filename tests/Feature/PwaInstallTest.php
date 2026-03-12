<?php

namespace Tests\Feature;

use Tests\CentralTestCase;

class PwaInstallTest extends CentralTestCase
{
    public function test_storefront_layout_has_pwa_install_prompt(): void
    {
        $layout = file_get_contents(resource_path('views/layouts/storefront.blade.php'));

        $this->assertStringContainsString('pwaInstall', $layout);
        $this->assertStringContainsString('beforeinstallprompt', $layout);
        $this->assertStringContainsString('pwaInstallBtn', $layout);
    }

    public function test_pwa_prompt_hidden_by_default(): void
    {
        $layout = file_get_contents(resource_path('views/layouts/storefront.blade.php'));

        $this->assertStringContainsString('id="pwaInstall" style="display:none', $layout);
    }

    public function test_pwa_prompt_has_dismiss_functionality(): void
    {
        $layout = file_get_contents(resource_path('views/layouts/storefront.blade.php'));

        $this->assertStringContainsString('dismissPwa', $layout);
        $this->assertStringContainsString('pwaDismissed', $layout);
    }

    public function test_manifest_link_exists_in_storefront(): void
    {
        $layout = file_get_contents(resource_path('views/layouts/storefront.blade.php'));

        $this->assertStringContainsString('rel="manifest"', $layout);
    }
}
