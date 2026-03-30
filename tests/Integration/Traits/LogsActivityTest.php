<?php

use App\Models\ActivityLog;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    $this->actingAs(User::factory()->owner()->create());
});

test('creating a model logs activity', function () {
    $product = Product::factory()->create();

    $log = ActivityLog::query()->where('model_type', Product::class)->where('action', 'created')->first();

    expect($log)->not->toBeNull()
        ->and($log->model_id)->toBe($product->id)
        ->and($log->action)->toBe('created');
});

test('updating a model logs activity with changes', function () {
    $product = Product::factory()->create(['name' => 'Old Name']);

    $product->update(['name' => 'New Name']);

    $log = ActivityLog::query()
        ->where('model_type', Product::class)
        ->where('action', 'updated')
        ->first();

    expect($log)->not->toBeNull()
        ->and($log->properties['changes']['name'])->toBe('New Name');
});
