<x-layouts.storefront>
    <section class="mx-auto max-w-md px-4 py-16">
        <div class="mb-8 text-center">
            <h1 class="font-display text-warm-900 mb-2 text-3xl md:text-4xl">Welcome back</h1>
            <p class="text-warm-600">Sign in to see your orders and favorites.</p>
        </div>

        <div class="card p-8">
            @if (session('status'))
                <div class="mb-5 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('account.login') }}" class="space-y-5" data-test="login-form">
                @csrf

                <x-storefront.form.field name="email" label="Email">
                    <x-storefront.form.input
                        type="email"
                        id="email"
                        name="email"
                        value="{{ old('email') }}"
                        required
                        autofocus
                        autocomplete="email"
                        data-test="login-form-email"
                    />
                </x-storefront.form.field>

                <x-storefront.form.field name="password" label="Password">
                    <x-storefront.form.input
                        type="password"
                        id="password"
                        name="password"
                        required
                        autocomplete="current-password"
                        data-test="login-form-password"
                    />
                </x-storefront.form.field>

                <div class="flex items-center justify-between">
                    <label class="text-warm-700 flex items-center gap-2 text-sm">
                        <input
                            type="checkbox"
                            name="remember"
                            value="1"
                            class="border-warm-300 rounded"
                            data-test="login-form-remember"
                        />
                        Keep me signed in
                    </label>
                    <a
                        href="{{ route('account.password.request') }}"
                        class="text-warm-700 hover:text-warm-900 text-sm font-semibold hover:underline"
                    >
                        Forgot password?
                    </a>
                </div>

                <x-storefront.buttons.primary type="submit" data-test="login-form-submit">
                    Sign in
                </x-storefront.buttons.primary>
            </form>

            <p class="text-warm-600 mt-6 text-center text-sm">
                Don't have an account?
                <a href="{{ route('account.register.show') }}" class="text-warm-800 font-semibold hover:underline"
                    >Create one</a>
            </p>
        </div>
    </section>
</x-layouts.storefront>
