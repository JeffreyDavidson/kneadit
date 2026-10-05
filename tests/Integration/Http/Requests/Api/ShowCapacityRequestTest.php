<?php

use App\Http\Requests\Api\ShowCapacityRequest;

test('date accepts a real Y-m-d date', function () {
    $validator = validator(['date' => '2026-10-05'], (new ShowCapacityRequest)->rules());

    expect($validator->passes())->toBeTrue();
});

test('date rejects anything that is not a real Y-m-d date', function (?string $date) {
    $validator = validator(['date' => $date], (new ShowCapacityRequest)->rules());

    expect($validator->errors()->has('date'))->toBeTrue();
})->with([
    'garbage' => 'garbage',
    'wrong order' => '10-05-2026',
    'impossible day' => '2026-02-31',
    'with time' => '2026-10-05 10:00',
    'missing' => null,
]);
