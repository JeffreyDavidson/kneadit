<?php

declare(strict_types=1);
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Builder;

arch('enums should implement HasLabel for Filament')
    ->expect('App\Enums')
    ->toImplement(HasLabel::class);

arch('custom query builders should extend Eloquent Builder')
    ->expect('App\Builders')
    ->toExtend(Builder::class);

arch('custom casts should implement CastsAttributes')
    ->expect('App\Casts')
    ->toImplement(CastsAttributes::class);

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
