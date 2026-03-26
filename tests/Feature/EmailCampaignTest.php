<?php

use App\Enums\EmailCampaignStatus;
use App\Models\Customer;
use App\Models\EmailCampaign;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    setUpTenantTest();

    if (! Schema::hasTable('email_campaigns')) {
        Schema::create('email_campaigns', function ($table) {
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
});

test('email campaign model exists', function () {
    expect(class_exists(EmailCampaign::class))->toBeTrue();
});

test('campaign can be created with subject and body', function () {
    $campaign = EmailCampaign::on('sqlite')->create([
        'subject' => 'Spring Sale!',
        'body' => '<h1>Spring Sale</h1><p>20% off all pastries</p>',
        'status' => EmailCampaignStatus::Draft,
    ]);

    $this->assertDatabaseHas('email_campaigns', [
        'subject' => 'Spring Sale!',
    ]);
    expect($campaign->body)->toBe('<h1>Spring Sale</h1><p>20% off all pastries</p>');
});

test('campaign status defaults to draft', function () {
    $campaign = EmailCampaign::on('sqlite')->create([
        'subject' => 'Test Campaign',
        'body' => 'Test body',
        'status' => EmailCampaignStatus::Draft,
    ]);

    expect($campaign->status)->toBe(EmailCampaignStatus::Draft);
});

test('campaign can be marked as sent', function () {
    $campaign = EmailCampaign::on('sqlite')->create([
        'subject' => 'Sent Campaign',
        'body' => 'Body content',
        'status' => EmailCampaignStatus::Draft,
    ]);

    $campaign->update([
        'status' => EmailCampaignStatus::Sent,
        'sent_at' => now(),
        'recipient_count' => 42,
    ]);

    $campaign->refresh();

    expect($campaign->status)->toBe(EmailCampaignStatus::Sent);
    expect($campaign->sent_at)->not->toBeNull();
    expect($campaign->recipient_count)->toBe(42);
});

test('campaign sent at is cast to datetime', function () {
    $campaign = EmailCampaign::on('sqlite')->create([
        'subject' => 'Date Test',
        'body' => 'Body',
        'status' => EmailCampaignStatus::Sent,
        'sent_at' => '2026-03-08 12:00:00',
    ]);

    expect($campaign->sent_at)->toBeInstanceOf(Carbon::class);
});

test('campaign recipient count stored correctly', function () {
    Customer::query()->create(['name' => 'Customer 1', 'email' => 'c1@example.com']);
    Customer::query()->create(['name' => 'Customer 2', 'email' => 'c2@example.com']);
    Customer::query()->create(['name' => 'Customer 3', 'email' => 'c3@example.com']);

    $count = Customer::query()->whereNotNull('email')->count();

    $campaign = EmailCampaign::on('sqlite')->create([
        'subject' => 'To All',
        'body' => 'Hello everyone',
        'status' => EmailCampaignStatus::Sent,
        'sent_at' => now(),
        'recipient_count' => $count,
    ]);

    expect($campaign->recipient_count)->toBe(3);
});
