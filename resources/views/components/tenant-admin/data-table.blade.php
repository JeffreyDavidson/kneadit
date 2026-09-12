@props([])

<div class="overflow-x-auto" {{ $attributes }}>
    <table class="w-full border-collapse text-[0.85rem]">
        {{ $slot }}
    </table>
</div>
