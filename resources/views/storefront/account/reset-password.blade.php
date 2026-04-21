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

                <label class="block">
                    <span class="text-sm font-medium text-warm-800">Email</span>
                    <input type="email" name="email" value="{{ old('email', $email) }}" required
                        autocomplete="email"
                        class="mt-1 w-full rounded-lg border border-warm-300 px-3 py-2 text-warm-900 outline-none focus:border-warm-500 focus:ring-2 focus:ring-warm-500/20">
                    @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </label>

                <label class="block">
                    <span class="text-sm font-medium text-warm-800">New password</span>
                    <input type="password" name="password" required autofocus
                        autocomplete="new-password"
                        class="mt-1 w-full rounded-lg border border-warm-300 px-3 py-2 text-warm-900 outline-none focus:border-warm-500 focus:ring-2 focus:ring-warm-500/20">
                    <p class="mt-1 text-xs text-warm-500">At least 8 characters, with letters and numbers.</p>
                    @error('password')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </label>

                <label class="block">
                    <span class="text-sm font-medium text-warm-800">Confirm new password</span>
                    <input type="password" name="password_confirmation" required
                        autocomplete="new-password"
                        class="mt-1 w-full rounded-lg border border-warm-300 px-3 py-2 text-warm-900 outline-none focus:border-warm-500 focus:ring-2 focus:ring-warm-500/20">
                </label>

                <x-storefront.buttons.primary type="submit">
                    Reset password
                </x-storefront.buttons.primary>
            </form>
        </div>
    </section>
</x-layouts.storefront>
