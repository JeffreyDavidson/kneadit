<?php

namespace Tests\Feature;

use App\Http\Middleware\EnsureStorefrontEnabled;
use App\Http\Middleware\TrackPageView;
use App\Models\BlockedDate;
use App\Models\BusinessSchedule;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomainOrSubdomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;
use Tests\TestCase;

class SchedulingTest extends TestCase
{
    use RefreshDatabase;

    protected array $tenantMiddleware;

    protected function setUp(): void
    {
        parent::setUp();
        config(['database.connections.central' => config('database.connections.sqlite')]);
        config(['tenancy.central_domains' => []]);
        $tenantMigrationPath = database_path('migrations/tenant');
        if (is_dir($tenantMigrationPath)) {
            $this->artisan('migrate', ['--path' => $tenantMigrationPath, '--realpath' => true]);
        }
        $this->tenantMiddleware = [
            InitializeTenancyByDomainOrSubdomain::class,
            PreventAccessFromCentralDomains::class,
            EnsureStorefrontEnabled::class,
            TrackPageView::class,
        ];
    }

    /** @test */
    public function availability_endpoint_returns_json(): void
    {
        $response = $this->withoutMiddleware($this->tenantMiddleware)->getJson('/availability');
        $response->assertOk();
        $response->assertJsonIsArray();
    }

    /** @test */
    public function blocked_dates_show_as_unavailable(): void
    {
        $tomorrow = today()->addDay();

        // Make sure the day of week is open
        BusinessSchedule::create([
            'day_of_week' => (int) $tomorrow->dayOfWeek,
            'is_open' => true,
            'open_time' => '08:00',
            'close_time' => '17:00',
        ]);

        // Insert directly to avoid Eloquent date cast adding time component in SQLite
        \DB::table('blocked_dates')->insert([
            'date' => $tomorrow->toDateString(),
            'reason' => 'Holiday',
            'is_all_day' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->withoutMiddleware($this->tenantMiddleware)->getJson('/availability');
        $response->assertOk();

        $dates = collect($response->json());
        $blockedEntry = $dates->firstWhere('date', $tomorrow->toDateString());
        $this->assertNotNull($blockedEntry);
        $this->assertFalse($blockedEntry['available']);
    }

    /** @test */
    public function closed_days_show_as_unavailable(): void
    {
        // Find the next occurrence of day_of_week 0 (Sunday) within 30 days
        $target = today();
        for ($i = 0; $i < 30; $i++) {
            $candidate = today()->addDays($i);
            if ((int) $candidate->dayOfWeek === 0) {
                $target = $candidate;
                break;
            }
        }

        BusinessSchedule::create([
            'day_of_week' => 0,
            'is_open' => false,
        ]);

        $response = $this->withoutMiddleware($this->tenantMiddleware)->getJson('/availability');
        $dates = collect($response->json());
        $entry = $dates->firstWhere('date', $target->toDateString());
        $this->assertNotNull($entry);
        $this->assertFalse($entry['available']);
        $this->assertEquals('Closed', $entry['reason']);
    }

    /** @test */
    public function open_days_show_as_available(): void
    {
        $tomorrow = today()->addDay();

        BusinessSchedule::create([
            'day_of_week' => (int) $tomorrow->dayOfWeek,
            'is_open' => true,
            'open_time' => '08:00',
            'close_time' => '17:00',
            'max_orders' => 50,
        ]);

        Setting::set('default_daily_capacity', '100');

        $response = $this->withoutMiddleware($this->tenantMiddleware)->getJson('/availability');
        $dates = collect($response->json());
        $entry = $dates->firstWhere('date', $tomorrow->toDateString());
        $this->assertNotNull($entry);
        $this->assertTrue($entry['available']);
    }

    /** @test */
    public function capacity_is_reflected_in_availability_response(): void
    {
        $tomorrow = today()->addDay();

        BusinessSchedule::create([
            'day_of_week' => (int) $tomorrow->dayOfWeek,
            'is_open' => true,
            'open_time' => '08:00',
            'close_time' => '17:00',
            'max_orders' => 10,
        ]);

        $response = $this->withoutMiddleware($this->tenantMiddleware)->getJson('/availability');
        $dates = collect($response->json());
        $entry = $dates->firstWhere('date', $tomorrow->toDateString());
        $this->assertArrayHasKey('remaining_capacity', $entry);
        $this->assertEquals(10, $entry['remaining_capacity']);
    }
}
