@props(['name'])

<div>
    <label for="{{ $name }}" class="block text-sm font-medium text-warm-800">{{ $label }}</label>
    {{ $slot }}
    <x-storefront.form.error :name="$name" />
</div>
