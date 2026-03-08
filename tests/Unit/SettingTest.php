<?php

namespace Tests\Unit;

use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['database.connections.central' => config('database.connections.sqlite')]);
        $tenantMigrationPath = database_path('migrations/tenant');
        if (is_dir($tenantMigrationPath)) {
            $this->artisan('migrate', ['--path' => $tenantMigrationPath, '--realpath' => true]);
        }
    }

    /** @test */
    public function get_returns_value_when_exists(): void
    {
        Setting::create(['key' => 'store_name', 'value' => 'Test Bakery']);
        $this->assertEquals('Test Bakery', Setting::get('store_name'));
    }

    /** @test */
    public function get_returns_default_when_not_exists(): void
    {
        $this->assertEquals('fallback', Setting::get('nonexistent', 'fallback'));
    }

    /** @test */
    public function get_returns_null_default(): void
    {
        $this->assertNull(Setting::get('nonexistent'));
    }

    /** @test */
    public function set_creates_new_setting(): void
    {
        Setting::set('new_key', 'new_value');
        $this->assertDatabaseHas('settings', ['key' => 'new_key', 'value' => 'new_value']);
    }

    /** @test */
    public function set_updates_existing_setting(): void
    {
        Setting::set('my_key', 'old');
        Setting::set('my_key', 'new');
        $this->assertEquals('new', Setting::get('my_key'));
        $this->assertEquals(1, Setting::where('key', 'my_key')->count());
    }
}
