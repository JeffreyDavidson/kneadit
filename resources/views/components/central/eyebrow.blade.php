@props(['as' => 'div'])

<{{ $as }}
    {{ $attributes->class(['text-(--kn-honey-text) text-[0.65rem] uppercase tracking-[0.1em] font-semibold']) }}
    >{{ $slot }}</{{ $as }}
>
