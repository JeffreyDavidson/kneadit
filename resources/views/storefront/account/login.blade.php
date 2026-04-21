<x-layouts.storefront>
    <section class="max-w-md mx-auto px-4 py-16">
        <div class="text-center mb-8">
            <h1 class="font-display text-3xl md:text-4xl text-warm-900 mb-2">Welcome back</h1>
            <p class="text-warm-600">Sign in to see your orders and favorites.</p>
        </div>

        <div class="card p-8">
            @if (session('status'))
                <div class="mb-5 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-800">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('account.login') }}" class="space-y-5" data-test="login-form">
                @csrf

                <label class="block">
                    <span class="text-sm font-medium text-warm-800">Email</span>
                    <input type="email" name="email" value="{{ old('email') }}" required autofocus
                        autocomplete="email"
                        data-test="login-form-email"
                        class="mt-1 w-full rounded-lg border border-warm-300 px-3 py-2 text-warm-900 outline-none focus:border-warm-500 focus:ring-2 focus:ring-warm-500/20">
                    <x-storefront.form.error name="email" />
                </label>

                <label class="block">
                    <span class="text-sm font-medium text-warm-800">Password</span>
                    <input type="password" name="password" required
                        autocomplete="current-password"
                        data-test="login-form-password"
                        class="mt-1 w-full rounded-lg border border-warm-300 px-3 py-2 text-warm-900 outline-none focus:border-warm-500 focus:ring-2 focus:ring-warm-500/20">
                    <x-storefront.form.error name="password" />
                </label>

                <div class="flex items-center justify-between">
                    <label class="flex items-center gap-2 text-sm text-warm-700">
                        <input type="checkbox" name="remember" value="1" class="rounded border-warm-300" data-test="login-form-remember">
                        Keep me signed in
                    </label>
                    <a href="{{ route('account.password.request') }}" class="text-sm font-semibold text-warm-700 hover:text-warm-900 hover:underline">
                        Forgot password?
                    </a>
                </div>

                <x-storefront.buttons.primary type="submit" data-test="login-form-submit">
                    Sign in
                </x-storefront.buttons.primary>
            </form>

            <p class="mt-6 text-center text-sm text-warm-600">
                Don't have an account?
                <a href="{{ route('account.register.show') }}" class="font-semibold text-warm-800 hover:underline">Create one</a>
            </p>
        </div>
    </section>
</x-layouts.storefront>
