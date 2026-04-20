<?php

namespace App\Filament\Forms\Components;

use Filament\Forms\Components\TextInput;

/**
 * Reusable contact field groupings for forms that capture customer
 * information (name, email, phone). Returns plain field arrays so each
 * caller can place them inside its own Grid/Section layout.
 */
class ContactFields
{
    /** @return array<int, TextInput> */
    public static function nameEmailPhone(): array
    {
        return [
            self::name(),
            self::email(),
            self::phone(),
        ];
    }

    /** @return array<int, TextInput> */
    public static function nameAndEmail(): array
    {
        return [
            self::name(),
            self::email(),
        ];
    }

    public static function name(): TextInput
    {
        return TextInput::make('customer_name')
            ->required()
            ->maxLength(255);
    }

    public static function email(): TextInput
    {
        return TextInput::make('customer_email')
            ->email()
            ->required()
            ->maxLength(255);
    }

    public static function phone(): TextInput
    {
        return TextInput::make('customer_phone')
            ->tel()
            ->maxLength(255);
    }
}
