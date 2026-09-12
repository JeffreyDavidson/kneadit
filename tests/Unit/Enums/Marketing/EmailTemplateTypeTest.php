<?php

use App\Enums\Marketing\EmailTemplateType;

test('all cases expose complete template metadata', function (EmailTemplateType $type) {
    $placeholders = $type->availablePlaceholders();

    expect($type->getLabel())->toBeString()->not->toBeEmpty()
        ->and($type->description())->toBeString()->not->toBeEmpty()
        ->and($type->defaultSubject())->toBeString()->not->toBeEmpty()
        ->and($placeholders)->toBeArray()->not->toBeEmpty()
        ->and($placeholders)->each->toMatch('/^\{.+\}$/')
        ->and($placeholders)->toContain('{customer_name}')
        ->and($placeholders)->toContain('{store_name}');
})->with(EmailTemplateType::cases());
