<?php

arch('mail classes must end with Mail')
    ->expect('App\Mail')
    ->toHaveSuffix('Mail')
    ->ignoring('App\Mail\BaseMailable')
    ->ignoring('App\Mail\Concerns');

arch('listener classes must end with Listener')
    ->expect('App\Listeners')
    ->toHaveSuffix('Listener');

arch('console command classes must end with Command')
    ->expect('App\Console\Commands')
    ->toHaveSuffix('Command');
