<?php

use App\Models\Engagement\EmailCampaign;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

const EMAIL_CAMPAIGN_LOG_INDEX = 'email_campaign_logs_campaign_email_unique';

beforeEach(function () {
    setUpCentralTest();
    Schema::table('email_campaign_logs', fn ($table) => $table->dropUnique(EMAIL_CAMPAIGN_LOG_INDEX));
});

function runEmailCampaignLogIndexMigration(): void
{
    $migration = require database_path('migrations/2026_10_04_120000_add_unique_recipient_index_to_email_campaign_logs_table.php');

    throw_unless($migration instanceof Migration, RuntimeException::class, 'Expected a Laravel migration.');

    $up = [$migration, 'up'];

    throw_unless(is_callable($up), RuntimeException::class, 'Expected a runnable Laravel migration.');

    $up();
}

function insertRawEmailCampaignLog(int $campaignId, string $email): int
{
    return DB::table('email_campaign_logs')->insertGetId([
        'campaign_id' => $campaignId,
        'tenant_id' => 'bakery',
        'email' => $email,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}

function emailCampaignLogIndexExists(): bool
{
    return collect(Schema::getIndexes('email_campaign_logs'))
        ->contains(fn (array $index): bool => $index['name'] === EMAIL_CAMPAIGN_LOG_INDEX && $index['unique']);
}

test('adds a unique index on campaign and recipient email', function () {
    runEmailCampaignLogIndexMigration();

    expect(emailCampaignLogIndexExists())->toBeTrue();
});

test('keeps the lowest id of a duplicate pair, leaves other rows alone and logs the count', function () {
    $logger = Log::spy();
    $campaign = EmailCampaign::factory()->create();
    $other = EmailCampaign::factory()->create();
    $first = insertRawEmailCampaignLog($campaign->id, 'a@example.com');
    insertRawEmailCampaignLog($campaign->id, 'a@example.com');
    $differentEmail = insertRawEmailCampaignLog($campaign->id, 'b@example.com');
    $differentCampaign = insertRawEmailCampaignLog($other->id, 'a@example.com');

    runEmailCampaignLogIndexMigration();

    expect(DB::table('email_campaign_logs')->orderBy('id')->pluck('id')->all())
        ->toBe([$first, $differentEmail, $differentCampaign])
        ->and(emailCampaignLogIndexExists())->toBeTrue();

    $logger->shouldHaveReceived('warning')->once()->withArgs(
        fn (string $message, array $context): bool => $context['removed'] === 1,
    );
});

test('running the migration twice changes nothing the second time', function () {
    Log::spy();
    $campaign = EmailCampaign::factory()->create();
    insertRawEmailCampaignLog($campaign->id, 'a@example.com');

    runEmailCampaignLogIndexMigration();
    runEmailCampaignLogIndexMigration();

    expect(DB::table('email_campaign_logs')->count())->toBe(1)
        ->and(emailCampaignLogIndexExists())->toBeTrue();
});
