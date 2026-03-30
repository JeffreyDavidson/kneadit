<?php

declare(strict_types=1);

arch('actions should be invokable')
    ->expect('App\Actions')
    ->toHaveMethod('__invoke');

arch('form requests should extend FormRequest')
    ->expect('App\Http\Requests')
    ->toExtend('Illuminate\Foundation\Http\FormRequest');

arch('services should be classes')
    ->expect('App\Services')
    ->toBeClasses();

arch('observers should end with Observer')
    ->expect('App\Observers')
    ->toHaveSuffix('Observer');

arch('enums should be string-backed')
    ->expect('App\Enums')
    ->toBeStringBackedEnums();

arch('controllers should not use env() directly')
    ->expect('env')
    ->not->toBeUsedIn('App\Http\Controllers');

arch('models should not use DB facade')
    ->expect('Illuminate\Support\Facades\DB')
    ->not->toBeUsedIn('App\Models');

arch('exceptions should be classes')
    ->expect('App\Exceptions')
    ->toBeClasses();

arch('mailables should extend BaseMailable')
    ->expect('App\Mail')
    ->toExtend('App\Mail\BaseMailable')
    ->ignoring(['App\Mail\BaseMailable', 'App\Mail\Concerns']);

arch('controllers should not use compact() for view data')
    ->expect('compact')
    ->not->toBeUsedIn('App\Http\Controllers');
