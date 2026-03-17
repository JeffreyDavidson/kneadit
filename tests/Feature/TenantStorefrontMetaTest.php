<?php

namespace Tests\Feature;

use Tests\CentralTestCase;

class TenantStorefrontMetaTest extends CentralTestCase
{
    // --- #24: Custom 404 page ---

    public function test_404_page_exists(): void
    {
        $this->assertTrue(
            file_exists(resource_path('views/errors/404.blade.php')),
            'Custom 404 view should exist'
        );
    }

    public function test_404_page_contains_bakery_themed_copy(): void
    {
        $content = file_get_contents(resource_path('views/errors/404.blade.php'));

        $this->assertStringContainsString('Nothing baking here', $content);
        $this->assertStringContainsString('404', $content);
    }

    public function test_nonexistent_route_returns_404(): void
    {
        $response = $this->get('/this-page-does-not-exist-at-all');

        $response->assertStatus(404);
    }

    // --- #22: OG meta tags ---

    public function test_storefront_layout_has_og_tags(): void
    {
        $layout = file_get_contents(resource_path('views/layouts/storefront.blade.php'));

        $this->assertStringContainsString('og:title', $layout);
        $this->assertStringContainsString('og:description', $layout);
        $this->assertStringContainsString('og:type', $layout);
        $this->assertStringContainsString('og:url', $layout);
        $this->assertStringContainsString('og:site_name', $layout);
        $this->assertStringContainsString('twitter:card', $layout);
    }

    public function test_storefront_layout_has_meta_description(): void
    {
        $layout = file_get_contents(resource_path('views/layouts/storefront.blade.php'));

        $this->assertStringContainsString('meta name="description"', $layout);
    }

    // --- #21: Dynamic favicon ---

    public function test_storefront_layout_has_favicon(): void
    {
        $layout = file_get_contents(resource_path('views/layouts/storefront.blade.php'));

        $this->assertStringContainsString('rel="icon"', $layout);
    }

    public function test_favicon_uses_store_logo_when_available(): void
    {
        $layout = file_get_contents(resource_path('views/layouts/storefront.blade.php'));

        $this->assertStringContainsString("Setting::get('store_logo')", $layout);
    }

    public function test_favicon_falls_back_to_svg_with_brand_color(): void
    {
        $layout = file_get_contents(resource_path('views/layouts/storefront.blade.php'));

        $this->assertStringContainsString('image/svg+xml', $layout);
        $this->assertStringContainsString('brand_color_primary', $layout);
    }

    // --- #23: Cookie consent ---

    public function test_storefront_layout_has_cookie_consent_banner(): void
    {
        $layout = file_get_contents(resource_path('views/layouts/storefront.blade.php'));

        $this->assertStringContainsString('cookieConsent', $layout);
        $this->assertStringContainsString('acceptCookies', $layout);
        $this->assertStringContainsString('localStorage', $layout);
    }

    public function test_cookie_consent_links_to_privacy_policy(): void
    {
        $layout = file_get_contents(resource_path('views/layouts/storefront.blade.php'));

        $this->assertStringContainsString('/privacy', $layout);
    }

    public function test_cookie_consent_hidden_by_default(): void
    {
        $layout = file_get_contents(resource_path('views/layouts/storefront.blade.php'));

        $this->assertStringContainsString('display:none', $layout);
    }
}
