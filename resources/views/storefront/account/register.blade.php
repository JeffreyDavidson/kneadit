<x-layouts.storefront>
    <section class="max-w-md mx-auto px-4 py-16">
        <div class="text-center mb-8">
            <h1 class="font-display text-3xl md:text-4xl text-warm-900 mb-2">Create an account</h1>
            <p class="text-warm-600">Save your favorites and track every order.</p>
        </div>

        <div class="card p-8">
            <form method="POST" action="{{ route('account.register') }}" class="space-y-5" data-test="register-form">
                @csrf

                <label class="block">
                    <span class="text-sm font-medium text-warm-800">Name</span>
                    <input type="text" name="name" value="{{ old('name') }}" required autofocus
                        autocomplete="name"
                        data-test="register-form-name"
                        class="mt-1 w-full rounded-lg border border-warm-300 px-3 py-2 text-warm-900 outline-none focus:border-warm-500 focus:ring-2 focus:ring-warm-500/20">
                    <x-storefront.form.error name="name" />
                </label>

                <label class="block">
                    <span class="text-sm font-medium text-warm-800">Email</span>
                    <input type="email" name="email" value="{{ old('email') }}" required
                        autocomplete="email"
                        data-test="register-form-email"
                        class="mt-1 w-full rounded-lg border border-warm-300 px-3 py-2 text-warm-900 outline-none focus:border-warm-500 focus:ring-2 focus:ring-warm-500/20">
                    <x-storefront.form.error name="email" />
                </label>

                <label class="block">
                    <span class="text-sm font-medium text-warm-800">Phone <span class="text-warm-500 font-normal">(optional)</span></span>
                    <input type="tel" name="phone" value="{{ old('phone') }}"
                        autocomplete="tel"
                        data-test="register-form-phone"
                        class="mt-1 w-full rounded-lg border border-warm-300 px-3 py-2 text-warm-900 outline-none focus:border-warm-500 focus:ring-2 focus:ring-warm-500/20">
                    <x-storefront.form.error name="phone" />
                </label>

                <label class="block">
                    <span class="text-sm font-medium text-warm-800">Password</span>
                    <input type="password" name="password" required
                        autocomplete="new-password"
                        data-test="register-form-password"
                        class="mt-1 w-full rounded-lg border border-warm-300 px-3 py-2 text-warm-900 outline-none focus:border-warm-500 focus:ring-2 focus:ring-warm-500/20">
                    <p class="mt-1 text-xs text-warm-500">At least 8 characters, with letters and numbers.</p>
                    <x-storefront.form.error name="password" />
                </label>

                <label class="block">
                    <span class="text-sm font-medium text-warm-800">Confirm password</span>
                    <input type="password" name="password_confirmation" required
                        autocomplete="new-password"
                        data-test="register-form-password-confirmation"
                        class="mt-1 w-full rounded-lg border border-warm-300 px-3 py-2 text-warm-900 outline-none focus:border-warm-500 focus:ring-2 focus:ring-warm-500/20">
                </label>

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
