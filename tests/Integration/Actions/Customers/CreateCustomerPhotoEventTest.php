<?php

use App\Actions\Customers\CreateCustomerPhoto;
use App\Events\Customers\CustomerPhotoSubmitted;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    Storage::fake('public');
});

test('CreateCustomerPhoto fires CustomerPhotoSubmitted', function () {
    Event::fake();

    $photo = resolve(CreateCustomerPhoto::class)(
        photo: UploadedFile::fake()->image('cake.jpg'),
        customerName: 'Alice',
        customerEmail: 'alice@example.com',
        caption: 'Loved my birthday cake!',
    );

    Event::assertDispatched(fn (CustomerPhotoSubmitted $e): bool => $e->photo->is($photo));
});
