<?php

use App\Http\Requests\AcceptInvitationRequest;

test('accept invitation passes with no data since fields are sometimes', function () {
    $request = new AcceptInvitationRequest;
    $validator = validator([], $request->rules());

    expect($validator->passes())->toBeTrue();
});

test('accept invitation rejects empty name when present', function () {
    $request = new AcceptInvitationRequest;
    $validator = validator(['name' => ''], $request->rules());

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->has('name'))->toBeTrue();
});

test('accept invitation rejects weak password when present', function () {
    $request = new AcceptInvitationRequest;
    $validator = validator([
        'password' => 'short1',
        'password_confirmation' => 'short1',
    ], $request->rules());

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->has('password'))->toBeTrue();
});
