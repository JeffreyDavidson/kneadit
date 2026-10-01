@props(['field'])

<div
    x-show="fieldErrors.{{ $field }}"
    x-text="fieldErrors.{{ $field }}"
    data-test="order-form-error-{{ $field }}"
    role="alert"
    class="mt-1 text-sm text-red-400"
></div>
