<?php

use App\Models\Engagement\CustomerCampaign;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

pest()->use(RefreshDatabase::class);

const CUSTOMER_CAMPAIGN_LOG_INDEX = 'customer_campaign_logs_campaign_email_unique';

beforeEach(function () {
    setUpTenantTest();
    Schema::table('customer_campaign_logs', fn ($table) => $table->dropUnique(CUSTOMER_CAMPAIGN_LOG_INDEX));
});

function runCustomerCampaignLogIndexMigration(): void
{
    $migration = require database_path('migrations/tenant/2026_10_04_120000_add_unique_recipient_index_to_customer_campaign_logs_table.php');

    throw_unless($migration instanceof Migration, RuntimeException::class, 'Expected a Laravel migration.');

    $up = [$migration, 'up'];

    throw_unless(is_callable($up), RuntimeException::class, 'Expected a runnable Laravel migration.');

    $up();
}

function insertRawCustomerCampaignLog(int $campaignId, string $email, string $token): int
{
    return DB::table('customer_campaign_logs')->insertGetId([
        'customer_campaign_id' => $campaignId,
        'customer_email' => $email,
        'tracking_token' => $token,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}

function customerCampaignLogIndexExists(): bool
{
    return collect(Schema::getIndexes('customer_campaign_logs'))
        ->contains(fn (array $index): bool => $index['name'] === CUSTOMER_CAMPAIGN_LOG_INDEX && $index['unique']);
}

test('adds a unique index on campaign and recipient email', function () {
    runCustomerCampaignLogIndexMigration();

    expect(customerCampaignLogIndexExists())->toBeTrue();
});

test('keeps the lowest id of a duplicate pair, leaves other rows alone and logs the count', function () {
    $logger = Log::spy();
    $campaign = CustomerCampaign::factory()->create();
    $other = CustomerCampaign::factory()->create();
    $first = insertRawCustomerCampaignLog($campaign->id, 'a@example.com', 'token-1');
    insertRawCustomerCampaignLog($campaign->id, 'a@example.com', 'token-2');
    insertRawCustomerCampaignLog($campaign->id, 'a@example.com', 'token-3');
    $differentEmail = insertRawCustomerCampaignLog($campaign->id, 'b@example.com', 'token-4');
    $differentCampaign = insertRawCustomerCampaignLog($other->id, 'a@example.com', 'token-5');

    runCustomerCampaignLogIndexMigration();

    expect(DB::table('customer_campaign_logs')->orderBy('id')->pluck('id')->all())
        ->toBe([$first, $differentEmail, $differentCampaign])
        ->and(customerCampaignLogIndexExists())->toBeTrue();

    $logger->shouldHaveReceived('warning')->once()->withArgs(
        fn (string $message, array $context): bool => $context['removed'] === 2,
    );
});

test('running the migration twice changes nothing the second time', function () {
    Log::spy();
    $campaign = CustomerCampaign::factory()->create();
    insertRawCustomerCampaignLog($campaign->id, 'a@example.com', 'token-1');

    runCustomerCampaignLogIndexMigration();
    runCustomerCampaignLogIndexMigration();

    expect(DB::table('customer_campaign_logs')->count())->toBe(1)
        ->and(customerCampaignLogIndexExists())->toBeTrue();
});
