<x-layouts.storefront>
    <section class="mx-auto max-w-md px-4 py-16">
        <div class="mb-8 text-center">
            <h1 class="font-display text-warm-900 mb-2 text-3xl md:text-4xl">Verify your email</h1>
            <p class="text-warm-600">We sent a verification link to your inbox.</p>
        </div>

        <div class="card space-y-5 p-8">
            @if (session('status'))
                <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                    {{ session('status') }}
                </div>
            @endif

            <p class="text-warm-700 text-sm leading-relaxed">
                Click the link in the email we just sent to <strong>{{ auth('customer')->user()->email }}</strong> to
                activate full access to your account — including any previous orders tied to this email.
            </p>

            <p class="text-warm-600 text-sm">Didn't get it? Check your spam folder, or request another.</p>

            <div class="flex items-center justify-between gap-3">
                <form method="POST" action="{{ route('account.email.verify.send') }}">
                    @csrf
                    <button
                        type="submit"
                        class="bg-warm-800 hover:bg-warm-900 rounded-full px-5 py-2.5 text-sm font-semibold text-white transition"
                    >
                        Resend verification email
                    </button>
                </form>
                <form method="POST" action="{{ route('account.logout') }}">
                    @csrf
                    <button type="submit" class="text-warm-700 hover:text-warm-900 text-sm font-semibold underline">
                        Sign out
                    </button>
                </form>
            </div>
        </div>
    </section>
</x-layouts.storefront>
