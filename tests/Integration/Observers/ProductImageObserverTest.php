<?php

use App\Models\ProductImage;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

// Bug #32: ProductImageObserver.syncPrimary() calls update() on ProductImage
// which triggers the saved observer again, causing infinite recursion.
// Fix: use updateQuietly() instead of update() in syncPrimary().
test('first image is set as primary on save')
    ->todo();
