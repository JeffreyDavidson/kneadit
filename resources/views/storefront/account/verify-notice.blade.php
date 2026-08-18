<x-layouts.storefront>
    <section class="max-w-md mx-auto px-4 py-16">
        <div class="text-center mb-8">
            <h1 class="font-display text-3xl md:text-4xl text-warm-900 mb-2">Verify your email</h1>
            <p class="text-warm-600">We sent a verification link to your inbox.</p>
        </div>

        <div class="card p-8 space-y-5">
            @if (session('status'))
                <div class="rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-800">
                    {{ session('status') }}
                </div>
            @endif

            <p class="text-sm text-warm-700 leading-relaxed">
                Click the link in the email we just sent to <strong>{{ auth('customer')->user()->email }}</strong> to
                activate full access to your account — including any previous orders tied to this email.
            </p>

            <p class="text-sm text-warm-600">
                Didn't get it? Check your spam folder, or request another.
            </p>

            <div class="flex items-center justify-between gap-3">
                <form method="POST" action="{{ route('account.email.verify.send') }}">
                    @csrf
                    <button type="submit" class="rounded-full bg-warm-800 text-white font-semibold py-2.5 px-5 hover:bg-warm-900 transition text-sm">
                        Resend verification email
                    </button>
                </form>
                <form method="POST" action="{{ route('account.logout') }}">
                    @csrf
                    <button type="submit" class="text-sm font-semibold text-warm-700 hover:text-warm-900 underline">
                        Sign out
                    </button>
                </form>
            </div>
        </div>
    </section>
</x-layouts.storefront>
