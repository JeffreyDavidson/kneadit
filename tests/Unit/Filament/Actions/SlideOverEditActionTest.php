<?php

use App\Filament\Actions\SlideOverEditAction;

test('SlideOverEditAction wires the update authorization ability', function () {
    $action = SlideOverEditAction::make('test');

    $reflection = new ReflectionClass($action);
    $property = $reflection->getProperty('authorization');
    $property->setAccessible(true);

    expect($property->getValue($action))
        ->toMatchArray([
            'type' => 'all',
            'abilities' => ['update'],
        ]);
});
