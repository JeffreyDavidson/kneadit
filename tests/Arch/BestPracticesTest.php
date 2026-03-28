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
