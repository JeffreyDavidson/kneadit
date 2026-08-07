@props(['name', 'label'])

<div>
    <x-storefront.form.label :for="$name">{!! $label !!}</x-storefront.form.label>
    {{ $slot }}
    <x-storefront.form.error :name="$name" />
</div>
