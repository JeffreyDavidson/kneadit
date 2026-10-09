{{-- App\Filament\Forms\Components\AddressInput: the shared `addressInput` Alpine component (resources/js/address-input.js), or a plain text box without a Google key. --}}
@use(Illuminate\View\ComponentAttributeBag)
@php
    $statePath = $getStatePath();
    $isDisabled = $isDisabled();
    $key = $getBrowserKey();
    $rows = $getRows();

    $binding = $key
        ? [
            'x-model' => 'value',
            'x-on:input' => 'search()',
            'x-on:keydown.down.prevent' => 'move(1)',
            'x-on:keydown.up.prevent' => 'move(-1)',
            'x-on:keydown.enter' => 'choose($event)',
            'x-on:keydown.escape' => 'escape($event)',
            'x-on:blur' => 'close()',
            'role' => 'combobox',
            'aria-autocomplete' => 'list',
            'x-bind:aria-expanded' => 'open',
            'x-bind:aria-controls' => 'listId',
            'x-bind:aria-activedescendant' => 'active >= 0 ? optionId(active) : null',
            'autocomplete' => 'off',
            'data-address-input' => true,
        ]
        : [$applyStateBindingModifiers('wire:model') => $statePath];

    $inputAttributes = new ComponentAttributeBag([
        'id' => $getId(),
        ...$binding,
        'placeholder' => $getPlaceholder(),
        'maxlength' => $getMaxLength(),
        'rows' => $rows,
        'disabled' => $isDisabled,
        'required' => $isRequired(),
        'class' => $rows ? 'kn-address-textarea' : 'fi-input',
    ]);
@endphp

<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    {{-- A textarea takes Filament's textarea styles; its wrapper must not clip the suggestions. --}}
    <x-filament::input.wrapper
        :disabled="$isDisabled"
        :valid="! $errors->has($statePath)"
        @class(['fi-fo-textarea kn-address-textarea-wrp' => $rows])
    >
        @if ($key)
            <div
                wire:ignore
                x-data="addressInput({
                    value: $wire.$entangle(@js($statePath), @js($isLive())),
                    key: @js($key),
                    country: @js($getCountry()),
                    parts: @js((object) $getPartStatePaths()),
                })"
                {{ $getExtraAttributeBag()->class(['kn-address-field']) }}
            >
                @if ($rows)
                    <textarea {{ $inputAttributes }}></textarea>
                @else
                    <input type="text" {{ $inputAttributes }} />
                @endif

                <x-address-suggestions />
            </div>
        @elseif ($rows)
            <textarea {{ $inputAttributes }}></textarea>
        @else
            <input type="text" {{ $inputAttributes }} />
        @endif
    </x-filament::input.wrapper>
</x-dynamic-component>
