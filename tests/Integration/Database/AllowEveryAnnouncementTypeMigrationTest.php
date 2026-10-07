<?php

use App\Enums\Platform\AnnouncementType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    setUpCentralTest();

    // The test helper hand-builds this table without its CHECK, so recreate the
    // table exactly as the original migration did.
    Schema::connection('central')->dropIfExists('platform_announcements');
    runAnnouncementMigrationFile('2026_03_10_145011_create_platform_announcements_table.php');
});

function runAnnouncementMigrationFile(string $file): void
{
    $migration = require database_path("migrations/{$file}");

    throw_unless($migration instanceof Migration, RuntimeException::class, 'Expected a Laravel migration.');

    $up = [$migration, 'up'];

    throw_unless(is_callable($up), RuntimeException::class, 'Expected a runnable Laravel migration.');

    $up();
}

function insertAnnouncementOfType(string $type): void
{
    DB::connection('central')->table('platform_announcements')->insert([
        'title' => "A {$type} announcement",
        'body' => 'Body',
        'type' => $type,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}

test('the original table rejects the holiday type', function () {
    insertAnnouncementOfType(AnnouncementType::Holiday->value);
})->throws(QueryException::class);

test('every announcement type can be stored after the migration', function (AnnouncementType $type) {
    runAnnouncementMigrationFile('2026_10_06_120000_allow_every_announcement_type_in_platform_announcements_table.php');

    insertAnnouncementOfType($type->value);

    expect(DB::connection('central')->table('platform_announcements')->where('type', $type->value)->count())->toBe(1);
})->with(AnnouncementType::cases());

test('the migration keeps the CHECK constraint, existing rows and the defaults', function () {
    insertAnnouncementOfType(AnnouncementType::Warning->value);

    runAnnouncementMigrationFile('2026_10_06_120000_allow_every_announcement_type_in_platform_announcements_table.php');

    expect(DB::connection('central')->table('platform_announcements')->pluck('type')->all())->toBe(['warning']);

    DB::connection('central')->table('platform_announcements')->insert([
        'title' => 'Defaults',
        'body' => 'Body',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $defaults = DB::connection('central')->table('platform_announcements')->where('title', 'Defaults')->first();

    expect($defaults->type)->toBe(AnnouncementType::Info->value)
        ->and($defaults->is_active)->toBeTruthy()
        ->and($defaults->is_dismissable)->toBeTruthy()
        ->and(fn () => insertAnnouncementOfType('not-a-type'))->toThrow(QueryException::class);
});
