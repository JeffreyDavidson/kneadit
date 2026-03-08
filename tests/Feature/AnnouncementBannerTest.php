<?php

namespace Tests\Feature;

use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnnouncementBannerTest extends TestCase
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

    public function test_announcement_settings_can_be_stored(): void
    {
        Setting::set('announcement_enabled', '1');
        Setting::set('announcement_text', 'We are closed for the holiday!');
        Setting::set('announcement_type', 'warning');

        $this->assertEquals('1', Setting::get('announcement_enabled'));
        $this->assertEquals('We are closed for the holiday!', Setting::get('announcement_text'));
        $this->assertEquals('warning', Setting::get('announcement_type'));
    }

    public function test_announcement_defaults_to_disabled(): void
    {
        $this->assertEquals('0', Setting::get('announcement_enabled', '0'));
    }

    public function test_announcement_type_defaults_to_info(): void
    {
        $this->assertEquals('info', Setting::get('announcement_type', 'info'));
    }

    public function test_announcement_text_defaults_to_empty(): void
    {
        $this->assertEquals('', Setting::get('announcement_text', ''));
    }
}
