@props([
    'id',
    'name' => null,
    'value' => null,
    'rows' => null,
    'inputClass' => '',
    'placeholder' => null,
    'required' => false,
])

@use(App\Support\PhoneNumber)
@use(Illuminate\View\ComponentAttributeBag)

{{--
    The one storefront address input: the shared `addressInput` Alpine
    component (resources/js/address-input.js), as the Filament AddressInput
    field uses. A picked Google suggestion fills the whole formatted address.
    Without a Google key it is a plain text box (or textarea with `rows`).

    Pass `name` for a classic form post, or x-model for an Alpine form (it
    lands on the root, which is x-modelable, or on the plain box).
--}}
@php
    $key = config('services.google_maps.browser_key') ?: null;

    $inputAttributes = new ComponentAttributeBag([
        'id' => $id,
        'name' => $name,
        'rows' => $rows,
        'placeholder' => $placeholder,
        'required' => $required,
        'data-test' => $attributes->get('data-test'),
        'class' => $inputClass,
    ]);
@endphp

@if ($key)
    <div
        x-data="addressInput({ value: @js($value ?? ''), key: @js($key), country: @js(strtolower(PhoneNumber::homeCountry())) })"
        x-modelable="value"
        {{ $attributes->except(['data-test'])->class(['kn-address-field']) }}
    >
        @php
            $inputAttributes = $inputAttributes->merge([
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
            ]);
        @endphp

        @if ($rows)
            <textarea {{ $inputAttributes }}>{{ $value }}</textarea>
        @else
            <input type="text" value="{{ $value }}" {{ $inputAttributes }} />
        @endif

        <x-address-suggestions />
    </div>
@elseif ($rows)
    <textarea {{ $inputAttributes->merge($attributes->except(['data-test'])->getAttributes()) }}>{{ $value }}</textarea>
@else
    <input
        type="text"
        value="{{ $value }}"
        {{ $inputAttributes->merge($attributes->except(['data-test'])->getAttributes()) }}
    />
@endif
