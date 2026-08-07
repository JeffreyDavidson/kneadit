@props(['for'])

<label for="{{ $for }}" {{ $attributes->class(['block text-sm font-medium text-warm-800']) }}> {{ $slot }} </label>
