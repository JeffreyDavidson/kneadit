<?php

namespace Tests\Feature;

use Tests\CentralTestCase;

class ServiceWorkerRemovedTest extends CentralTestCase
{
    public function test_service_worker_route_returns_404(): void
    {
        $response = $this->get('/service-worker.js');

        $response->assertNotFound();
    }

    public function test_storefront_layout_does_not_register_service_worker(): void
    {
        $layout = file_get_contents(resource_path('views/layouts/storefront.blade.php'));

        $this->assertStringNotContainsString('serviceWorker', $layout);
        $this->assertStringNotContainsString('service-worker.js', $layout);
    }
}
