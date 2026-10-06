@props(['as' => 'div'])

<{{ $as }}
    {{ $attributes->class(['text-(--kn-muted) text-[0.7rem] uppercase tracking-[0.05em] font-semibold']) }}
    >{{ $slot }}</{{ $as }}
>
