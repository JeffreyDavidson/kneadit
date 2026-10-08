{{-- App\Filament\Forms\Components\PhoneInput: the shared `phoneInput` Alpine component (resources/js/phone-input.js). --}}
@use(App\Support\PhoneNumber)
@php
    $statePath = $getStatePath();
    $isDisabled = $isDisabled();
    $state = $getState();
@endphp

<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <x-filament::input.wrapper :disabled="$isDisabled" :valid="! $errors->has($statePath)">
        <div
            wire:ignore
            x-data="phoneInput({
                value: $wire.$entangle(@js($statePath), @js($isLive())),
                country: @js($getDefaultCountry()),
            })"
            {{ $getExtraAttributeBag()->class(['kn-phone-field']) }}
        >
            <input
                type="tel"
                x-ref="input"
                id="{{ $getId() }}"
                autocomplete="tel"
                inputmode="tel"
                {{-- Shown formatted before the browser's formatting utils have loaded. --}}
                value="{{ PhoneNumber::display(is_string($state) ? $state : null) }}"
                data-phone-input
                @disabled($isDisabled)
                @required($isRequired())
                class="fi-input"
            />
        </div>
    </x-filament::input.wrapper>
</x-dynamic-component>
