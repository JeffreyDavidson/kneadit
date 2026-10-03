<?php

use App\Models\Staff\User;
use Illuminate\Support\Facades\File;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

beforeEach(function () {
    setUpCentralTest();

    test()->backupRoot = sys_get_temp_dir().'/kneadit-backup-test-'.bin2hex(random_bytes(6));
    config(['backups.path' => test()->backupRoot]);

    // The download zips into a shared temp file named after the backup, so each test
    // gets its own random timestamp to stay safe when tests run in parallel.
    test()->backupName = now()->subDays(random_int(1, 3000))->format('Y-m-d_H-i-s');

    File::ensureDirectoryExists(test()->backupRoot.'/'.test()->backupName);
    File::put(test()->backupRoot.'/'.test()->backupName.'/central.sqlite', 'central database');
});

afterEach(function () {
    File::deleteDirectory(test()->backupRoot);
});

test('a guest is redirected to the login page', function () {
    get(route('central.backups.download', test()->backupName))
        ->assertRedirect(route('login'));
});

test('a bakery owner who is not a platform admin is forbidden from downloading a backup', function () {
    $owner = User::factory()->owner()->create();

    $response = actingAs($owner)
        ->get(route('central.backups.download', test()->backupName));

    $response->assertForbidden();
    expect($response->baseResponse)->not->toBeInstanceOf(BinaryFileResponse::class);
});

test('a platform admin can download an existing backup as a zip', function () {
    $admin = User::factory()->platformAdmin()->create();

    $response = actingAs($admin)
        ->get(route('central.backups.download', test()->backupName));

    $response
        ->assertOk()
        ->assertDownload('kneadit-backup-'.test()->backupName.'.zip');
});

test('a platform admin gets a 404 for a backup that does not exist', function () {
    $admin = User::factory()->platformAdmin()->create();

    actingAs($admin)
        ->get(route('central.backups.download', '2000-01-01_00-00-00'))
        ->assertNotFound();
});

test('a platform admin gets a 404 for an unsafe backup name', function (string $name) {
    $admin = User::factory()->platformAdmin()->create();

    actingAs($admin)
        ->get(route('central.backups.download', $name))
        ->assertNotFound();
})->with([
    'parent directory' => '..',
    'parent directory traversal' => '../etc/passwd',
    'traversal after a valid name' => '2026-04-25_12-34-56/../../..',
    'absolute path' => '/etc/passwd',
    'single slash' => '/',
    'not a timestamp' => 'not-a-timestamp',
    'valid timestamp with a suffix' => '2026-04-25_12-34-56.zip',
    'null byte' => "2026-04-25_12-34-56\0",
]);
