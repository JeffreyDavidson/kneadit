<?php

use App\Casts\PhoneNumberCast;
use Illuminate\Database\Eloquent\Model;

test('phone number cast normalizes on set', function () {
    $cast = new PhoneNumberCast;
    $model = new class extends Model {};

    expect($cast->set($model, 'phone', '(555) 123-4567', []))->toBe('5551234567')
        ->and($cast->set($model, 'phone', '555.123.4567', []))->toBe('5551234567')
        ->and($cast->set($model, 'phone', '555 123 4567', []))->toBe('5551234567')
        ->and($cast->set($model, 'phone', null, []))->toBeNull();
});
