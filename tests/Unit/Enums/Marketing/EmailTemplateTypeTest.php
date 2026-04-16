<?php

use App\Enums\Marketing\EmailTemplateType;

test('all cases have labels', function (EmailTemplateType $type) {
    expect($type->getLabel())->toBeString()->not->toBeEmpty();
})->with(EmailTemplateType::cases());

test('all cases have descriptions', function (EmailTemplateType $type) {
    expect($type->description())->toBeString()->not->toBeEmpty();
})->with(EmailTemplateType::cases());

test('all cases have available placeholders', function (EmailTemplateType $type) {
    $placeholders = $type->availablePlaceholders();

    expect($placeholders)->toBeArray()->not->toBeEmpty()
        ->and($placeholders)->each->toMatch('/^\{.+\}$/');
})->with(EmailTemplateType::cases());

test('all cases have default subjects', function (EmailTemplateType $type) {
    expect($type->defaultSubject())->toBeString()->not->toBeEmpty();
})->with(EmailTemplateType::cases());

test('all cases include store_name and customer_name placeholders', function (EmailTemplateType $type) {
    $placeholders = $type->availablePlaceholders();

    expect($placeholders)->toContain('{customer_name}')
        ->and($placeholders)->toContain('{store_name}');
})->with(EmailTemplateType::cases());
