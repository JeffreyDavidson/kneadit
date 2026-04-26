@php
    $user = filament()->auth()->user();
@endphp

<div class="fi-central-user-menu-header">
    <x-filament-panels::avatar.user :user="$user" loading="lazy" />

    <div class="fi-central-user-menu-header-text">
        <div class="fi-central-user-menu-header-name">{{ $user->name }}</div>
        <div class="fi-central-user-menu-header-email">{{ $user->email }}</div>
        <div class="fi-central-user-menu-header-role">{{ $user->role->getLabel() }}</div>
    </div>
</div>
