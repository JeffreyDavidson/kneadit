@php
    /** @var string $token */
    /** @var string $email */
@endphp
<x-layouts.storefront>
    <section class="max-w-md mx-auto px-4 py-16">
        <div class="text-center mb-8">
            <h1 class="font-display text-3xl md:text-4xl text-warm-900 mb-2">Choose a new password</h1>
        </div>

        <div class="card p-8">
            <form method="POST" action="{{ route('account.password.update') }}" class="space-y-5">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">

                <x-storefront.form.field name="email">
                    <x-slot:label>Email</x-slot:label>
                    <input type="email" id="email" name="email" value="{{ old('email', $email) }}" required
                        autocomplete="email"
                        class="mt-1 w-full rounded-lg border border-warm-300 px-3 py-2 text-warm-900 outline-none focus:border-warm-500 focus:ring-2 focus:ring-warm-500/20">
                </x-storefront.form.field>

                <x-storefront.form.field name="password">
                    <x-slot:label>New password</x-slot:label>
                    <input type="password" id="password" name="password" required autofocus
                        autocomplete="new-password"
                        class="mt-1 w-full rounded-lg border border-warm-300 px-3 py-2 text-warm-900 outline-none focus:border-warm-500 focus:ring-2 focus:ring-warm-500/20">
                    <p class="mt-1 text-xs text-warm-500">At least 8 characters, with letters and numbers.</p>
                </x-storefront.form.field>

                <x-storefront.form.field name="password_confirmation">
                    <x-slot:label>Confirm new password</x-slot:label>
                    <input type="password" id="password_confirmation" name="password_confirmation" required
                        autocomplete="new-password"
                        class="mt-1 w-full rounded-lg border border-warm-300 px-3 py-2 text-warm-900 outline-none focus:border-warm-500 focus:ring-2 focus:ring-warm-500/20">
                </x-storefront.form.field>

                <x-storefront.buttons.primary type="submit">
                    Reset password
                </x-storefront.buttons.primary>
            </form>
        </div>
    </section>
</x-layouts.storefront>
