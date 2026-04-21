<x-layouts.storefront>
    <section class="max-w-md mx-auto px-4 py-16">
        <div class="text-center mb-8">
            <h1 class="font-display text-3xl md:text-4xl text-warm-900 mb-2">Create an account</h1>
            <p class="text-warm-600">Save your favorites and track every order.</p>
        </div>

        <div class="card p-8">
            <form method="POST" action="{{ route('account.register') }}" class="space-y-5" data-test="register-form">
                @csrf

                <x-storefront.form.field name="name" label="Name">
                    <input type="text" id="name" name="name" value="{{ old('name') }}" required autofocus
                        autocomplete="name"
                        data-test="register-form-name"
                        class="mt-1 w-full rounded-lg border border-warm-300 px-3 py-2 text-warm-900 outline-none focus:border-warm-500 focus:ring-2 focus:ring-warm-500/20">
                </x-storefront.form.field>

                <x-storefront.form.field name="email" label="Email">
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required
                        autocomplete="email"
                        data-test="register-form-email"
                        class="mt-1 w-full rounded-lg border border-warm-300 px-3 py-2 text-warm-900 outline-none focus:border-warm-500 focus:ring-2 focus:ring-warm-500/20">
                </x-storefront.form.field>

                <x-storefront.form.field name="phone" label='Phone <span class="text-warm-500 font-normal">(optional)</span>'>
                    <input type="tel" id="phone" name="phone" value="{{ old('phone') }}"
                        autocomplete="tel"
                        data-test="register-form-phone"
                        class="mt-1 w-full rounded-lg border border-warm-300 px-3 py-2 text-warm-900 outline-none focus:border-warm-500 focus:ring-2 focus:ring-warm-500/20">
                </x-storefront.form.field>

                <x-storefront.form.field name="password" label="Password">
                    <input type="password" id="password" name="password" required
                        autocomplete="new-password"
                        data-test="register-form-password"
                        class="mt-1 w-full rounded-lg border border-warm-300 px-3 py-2 text-warm-900 outline-none focus:border-warm-500 focus:ring-2 focus:ring-warm-500/20">
                    <x-storefront.form.help>At least 8 characters, with letters and numbers.</x-storefront.form.help>
                </x-storefront.form.field>

                <x-storefront.form.field name="password_confirmation" label="Confirm password">
                    <input type="password" id="password_confirmation" name="password_confirmation" required
                        autocomplete="new-password"
                        data-test="register-form-password-confirmation"
                        class="mt-1 w-full rounded-lg border border-warm-300 px-3 py-2 text-warm-900 outline-none focus:border-warm-500 focus:ring-2 focus:ring-warm-500/20">
                </x-storefront.form.field>

                <x-storefront.buttons.primary type="submit" data-test="register-form-submit">
                    Create account
                </x-storefront.buttons.primary>
            </form>

            <p class="mt-6 text-center text-sm text-warm-600">
                Already have an account?
                <a href="{{ route('account.login.show') }}" class="font-semibold text-warm-800 hover:underline">Sign in</a>
            </p>
        </div>
    </section>
</x-layouts.storefront>
