@php
/** @var array<int, int> $userIds */
@endphp

These platform accounts have an active, trialing or past due subscription but no bakery linked to them.
Look each one up by id in the central admin and in Stripe.

User ids: {{ implode(', ', $userIds) }}
