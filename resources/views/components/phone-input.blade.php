@props([
    'id',
    'name' => null,
    'value' => null,
    'inputClass' => '',
    'placeholder' => null,
    'required' => false,
])

@use(App\Support\PhoneNumber)

{{--
    The one storefront phone input: the shared `phoneInput` Alpine component
    (resources/js/phone-input.js), as the Filament PhoneInput field uses.

    Pass `name` for a classic form post: a hidden input sends E.164 and the
    visible input only keeps the name when JavaScript doesn't run. Pass
    x-model (it lands on the root, which is x-modelable) for an Alpine form.
--}}
<div
    x-data="phoneInput({ value: @js($value ?? ''), country: @js(strtolower(PhoneNumber::homeCountry())) })"
    x-modelable="value"
    {{ $attributes->except(['data-test'])->class(['kn-phone-field']) }}
>
    <input
        type="tel"
        x-ref="input"
        id="{{ $id }}"
        @if ($name)
            name="{{ $name }}"
            x-bind:name="false"
        @endif
        {{-- Shown formatted before the browser's formatting utils have loaded. --}}
        value="{{ PhoneNumber::display($value) }}"
        autocomplete="tel"
        inputmode="tel"
        data-phone-input
        @if ($attributes->has('data-test')) data-test="{{ $attributes->get('data-test') }}" @endif
        @if ($placeholder) placeholder="{{ $placeholder }}" @endif
        @required($required)
        class="{{ $inputClass }}"
    />

    @if ($name)
        <input type="hidden" x-bind:name="@js($name)" x-bind:value="value" />
    @endif
</div>
