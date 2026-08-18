<?php

namespace Tests;

use App\Services\Settings\SettingsManager;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Model::preventLazyLoading();

        $this->withoutVite();
        resolve(SettingsManager::class)->flushCache();
    }
}
