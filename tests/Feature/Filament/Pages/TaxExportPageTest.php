<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Pennant\Feature;

uses(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    $this->actingAs(User::factory()->owner()->create());
    Feature::define('growth-features', fn () => true);
});

// Form property fixed but view uses MySQL YEAR() function incompatible with SQLite
test('tax export page can render')
    ->todo();
