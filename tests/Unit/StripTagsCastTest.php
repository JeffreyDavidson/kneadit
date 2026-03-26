<?php

use App\Casts\StripTagsCast;
use Illuminate\Database\Eloquent\Model;

it('strips HTML tags when setting a value', function () {
    $cast = new StripTagsCast;
    $model = new class extends Model {};

    $result = $cast->set($model, 'comment', '<script>alert("xss")</script>Hello', []);

    expect($result)->toBe('alert("xss")Hello');
});
