<?php

declare(strict_types=1);

arch('enums should implement HasLabel for Filament')
    ->expect('App\Enums')
    ->toImplement('Filament\Support\Contracts\HasLabel');

arch('custom query builders should extend Eloquent Builder')
    ->expect('App\Builders')
    ->toExtend('Illuminate\Database\Eloquent\Builder');

arch('custom casts should implement CastsAttributes')
    ->expect('App\Casts')
    ->toImplement('Illuminate\Contracts\Database\Eloquent\CastsAttributes');

arch('value objects should be final readonly')
    ->expect('App\ValueObjects')
    ->toBeFinal()
    ->toBeReadonly();

arch('DTOs should be final readonly')
    ->expect('App\DataTransferObjects')
    ->toBeFinal()
    ->toBeReadonly();

arch('query objects should be classes')
    ->expect('App\Queries')
    ->toBeClasses();
