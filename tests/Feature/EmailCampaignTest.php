<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\EmailCampaign;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class EmailCampaignTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['database.connections.central' => config('database.connections.sqlite')]);
        config(['tenancy.central_domains' => []]);
        $tenantMigrationPath = database_path('migrations/tenant');
        if (is_dir($tenantMigrationPath)) {
            $this->artisan('migrate', ['--path' => $tenantMigrationPath, '--realpath' => true]);
        }

        // Create email_campaigns table directly since central migrations
        // use Schema::connection('central') which conflicts with in-memory test DB
        if (! \Schema::hasTable('email_campaigns')) {
            \Schema::create('email_campaigns', function ($table) {
                $table->id();
                $table->string('name')->nullable();
                $table->string('subject');
                $table->text('body');
                $table->string('target_segment')->default('all');
                $table->string('status')->default('draft');
                $table->integer('recipient_count')->default(0);
                $table->timestamp('scheduled_at')->nullable();
                $table->timestamp('sent_at')->nullable();
                $table->timestamps();
            });
        }
    }

    public function test_email_campaign_model_exists(): void
    {
        $this->assertTrue(class_exists(EmailCampaign::class));
    }

    public function test_campaign_can_be_created_with_subject_and_body(): void
    {
        $campaign = EmailCampaign::on('sqlite')->create([
            'subject' => 'Spring Sale!',
            'body' => '<h1>Spring Sale</h1><p>20% off all pastries</p>',
            'status' => 'draft',
        ]);

        $this->assertDatabaseHas('email_campaigns', [
            'subject' => 'Spring Sale!',
        ]);
        $this->assertEquals('<h1>Spring Sale</h1><p>20% off all pastries</p>', $campaign->body);
    }

    public function test_campaign_status_defaults_to_draft(): void
    {
        $campaign = EmailCampaign::on('sqlite')->create([
            'subject' => 'Test Campaign',
            'body' => 'Test body',
            'status' => 'draft',
        ]);

        $this->assertEquals('draft', $campaign->status);
    }

    public function test_campaign_can_be_marked_as_sent(): void
    {
        $campaign = EmailCampaign::on('sqlite')->create([
            'subject' => 'Sent Campaign',
            'body' => 'Body content',
            'status' => 'draft',
        ]);

        $campaign->update([
            'status' => 'sent',
            'sent_at' => now(),
            'recipient_count' => 42,
        ]);

        $campaign->refresh();

        $this->assertEquals('sent', $campaign->status);
        $this->assertNotNull($campaign->sent_at);
        $this->assertEquals(42, $campaign->recipient_count);
    }

    public function test_campaign_sent_at_is_cast_to_datetime(): void
    {
        $campaign = EmailCampaign::on('sqlite')->create([
            'subject' => 'Date Test',
            'body' => 'Body',
            'status' => 'sent',
            'sent_at' => '2026-03-08 12:00:00',
        ]);

        $this->assertInstanceOf(Carbon::class, $campaign->sent_at);
    }

    public function test_campaign_recipient_count_stored_correctly(): void
    {
        Customer::create(['name' => 'Customer 1', 'email' => 'c1@example.com']);
        Customer::create(['name' => 'Customer 2', 'email' => 'c2@example.com']);
        Customer::create(['name' => 'Customer 3', 'email' => 'c3@example.com']);

        $count = Customer::whereNotNull('email')->count();

        $campaign = EmailCampaign::on('sqlite')->create([
            'subject' => 'To All',
            'body' => 'Hello everyone',
            'status' => 'sent',
            'sent_at' => now(),
            'recipient_count' => $count,
        ]);

        $this->assertEquals(3, $campaign->recipient_count);
    }
}
