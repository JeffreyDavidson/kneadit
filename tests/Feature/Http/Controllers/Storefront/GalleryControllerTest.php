<?php

use App\Models\CustomerPhoto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\withoutMiddleware;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('can submit a gallery photo', function () {
    Storage::fake('public');

    $response = withoutMiddleware(tenantMiddleware())
        ->post(route('gallery.submit', [], false), [
            'customer_name' => 'Jane Doe',
            'customer_email' => 'jane@example.com',
            'caption' => 'My birthday cake!',
            'photo' => UploadedFile::fake()->image('cake.jpg'),
        ]);

    $response->assertRedirect();

    expect(CustomerPhoto::query()->count())->toBe(1)
        ->and(CustomerPhoto::query()->first()->customer_name)->toBe('Jane Doe');
});
