<x-layouts.storefront>
    <section class="max-w-md mx-auto px-4 py-16">
        <div class="text-center mb-8">
            <h1 class="font-display text-3xl md:text-4xl text-warm-900 mb-2">Forgot your password?</h1>
            <p class="text-warm-600">We'll email you a link to reset it.</p>
        </div>

        <div class="card p-8">
            @if (session('status'))
                <div class="mb-5 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-800">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('account.password.email') }}" class="space-y-5" data-test="forgot-password-form">
                @csrf

                <x-storefront.form.field name="email" label="Email">
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                        autocomplete="email"
                        data-test="forgot-password-form-email"
                        class="mt-1 w-full rounded-lg border border-warm-300 px-3 py-2 text-warm-900 outline-none focus:border-warm-500 focus:ring-2 focus:ring-warm-500/20">
                </x-storefront.form.field>

                <x-storefront.buttons.primary type="submit" data-test="forgot-password-form-submit">
                    Send reset link
                </x-storefront.buttons.primary>
            </form>

            <p class="mt-6 text-center text-sm text-warm-600">
                <a href="{{ route('account.login.show') }}" class="font-semibold text-warm-800 hover:underline">Back to sign in</a>
            </p>
        </div>
    </section>
</x-layouts.storefront>
