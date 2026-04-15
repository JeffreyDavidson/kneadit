<?php

arch('mail classes must end with Mail')
    ->expect('App\Mail')
    ->toHaveSuffix('Mail')
    ->ignoring(App\Mail\BaseMailable::class)
    ->ignoring('App\Mail\Concerns');

arch('listener classes must end with Listener')
    ->expect('App\Listeners')
    ->toHaveSuffix('Listener');

arch('console command classes must end with Command')
    ->expect('App\Console\Commands')
    ->toHaveSuffix('Command');

arch('controllers must end with Controller')
    ->expect('App\Http\Controllers')
    ->toHaveSuffix('Controller')
    ->ignoring('App\Http\Controllers\Stripe\Concerns');

arch('policies must end with Policy')
    ->expect('App\Policies')
    ->toHaveSuffix('Policy');

arch('form requests must end with Request')
    ->expect('App\Http\Requests')
    ->toHaveSuffix('Request');

arch('events should not end with Event')
    ->expect('App\Events')
    ->not->toHaveSuffix('Event');
