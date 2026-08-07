<x-layouts.storefront>
    <section class="mx-auto max-w-md px-4 py-16">
        <div class="mb-8 text-center">
            <h1 class="font-display text-warm-900 mb-2 text-3xl md:text-4xl">Forgot your password?</h1>
            <p class="text-warm-600">We'll email you a link to reset it.</p>
        </div>

        <div class="card p-8">
            @if (session('status'))
                <div class="mb-5 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                    {{ session('status') }}
                </div>
            @endif

            <form
                method="POST"
                action="{{ route('account.password.email') }}"
                class="space-y-5"
                data-test="forgot-password-form"
            >
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
                        data-test="forgot-password-form-email"
                    />
                </x-storefront.form.field>

                <x-storefront.buttons.primary type="submit" data-test="forgot-password-form-submit">
                    Send reset link
                </x-storefront.buttons.primary>
            </form>

            <p class="text-warm-600 mt-6 text-center text-sm">
                <a href="{{ route('account.login.show') }}" class="text-warm-800 font-semibold hover:underline"
                    >Back to sign in</a>
            </p>
        </div>
    </section>
</x-layouts.storefront>
