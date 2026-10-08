<?php

use App\Casts\PhoneNumberCast;
use Illuminate\Database\Eloquent\Model;

test('phone number cast stores E.164 on set', function (?string $typed, ?string $stored) {
    $cast = new PhoneNumberCast;
    $model = new class extends Model {};

    $result = $cast->set($model, 'phone', $typed, []);

    expect($result)->toBe($stored);
})->with([
    'US national format' => ['(913) 387-7359', '+19133877359'],
    'US with dots' => ['913.387.7359', '+19133877359'],
    'US digits only' => ['9133877359', '+19133877359'],
    'US with the leading 1' => ['1 913 387 7359', '+19133877359'],
    'UK in E.164' => ['+44 20 7946 0958', '+442079460958'],
    'already E.164' => ['+19133877359', '+19133877359'],
    'too short to read is kept as typed' => ['555-0100', '555-0100'],
    'letters are kept as typed' => ['call the shop', 'call the shop'],
    'blank becomes null' => ['  ', null],
    'null stays null' => [null, null],
]);
