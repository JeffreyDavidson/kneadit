<?php

namespace Tests;

use App\Services\Settings\SettingsManager;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        resolve(SettingsManager::class)->flushCache();
    }
}
