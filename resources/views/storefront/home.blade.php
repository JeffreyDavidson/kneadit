@php
    /** @var \App\Models\Customers\Customer $customer */
@endphp
<x-layouts.storefront>
    <section class="mx-auto max-w-2xl px-4 py-12">
        <div class="mb-8 flex items-center justify-between">
            <h1 class="font-display text-warm-900 text-3xl">Your profile</h1>
            <a href="{{ route('account.dashboard') }}" class="text-warm-700 text-sm font-semibold hover:underline">
                &larr; Back to dashboard
            </a>
        </div>

        @if (session('status'))
            <div class="mb-6 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                {{ session('status') }}
            </div>
        @endif

        <div class="card p-6">
            <p class="text-warm-500 mb-6 text-sm">
                Email: <strong class="text-warm-700">{{ $customer->email }}</strong>
                <span class="text-warm-500 text-xs">— to change your email, please contact us.</span>
            </p>

            <form method="POST" action="{{ route('account.profile.update') }}" class="space-y-5">
                @csrf

                <div>
                    <label for="profile-name" class="text-warm-700 mb-1 block text-sm font-semibold">Name</label>
                    <input
                        id="profile-name"
                        type="text"
                        name="name"
                        value="{{ old('name', $customer->name) }}"
                        required
                        maxlength="255"
                        class="input-field w-full"
                    />
                    @error('name')
                        <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="profile-phone" class="text-warm-700 mb-1 block text-sm font-semibold">Phone</label>
                    <input
                        id="profile-phone"
                        type="tel"
                        name="phone"
                        value="{{ old('phone', $customer->phone) }}"
                        maxlength="20"
                        class="input-field w-full"
                    />
                    @error('phone')
                        <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="profile-birthday" class="text-warm-700 mb-1 block text-sm font-semibold"
                        >Birthday</label>
                    <input
                        id="profile-birthday"
                        type="date"
                        name="birthday"
                        value="{{ old('birthday', optional($customer->birthday)->format('Y-m-d')) }}"
                        class="input-field w-full"
                    />
                    @error('birthday')
                        <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="profile-address" class="text-warm-700 mb-1 block text-sm font-semibold">Address</label>
                    <input
                        id="profile-address"
                        type="text"
                        name="address"
                        value="{{ old('address', $customer->address) }}"
                        maxlength="255"
                        class="input-field w-full"
                    />
                    @error('address')
                        <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid gap-3 sm:grid-cols-3">
                    <div>
                        <label for="profile-city" class="text-warm-700 mb-1 block text-sm font-semibold">City</label>
                        <input
                            id="profile-city"
                            type="text"
                            name="city"
                            value="{{ old('city', $customer->city) }}"
                            maxlength="100"
                            class="input-field w-full"
                        />
                    </div>
                    <div>
                        <label for="profile-state" class="text-warm-700 mb-1 block text-sm font-semibold">State</label>
                        <input
                            id="profile-state"
                            type="text"
                            name="state"
                            value="{{ old('state', $customer->state) }}"
                            maxlength="100"
                            class="input-field w-full"
                        />
                    </div>
                    <div>
                        <label for="profile-zip" class="text-warm-700 mb-1 block text-sm font-semibold">Zip</label>
                        <input
                            id="profile-zip"
                            type="text"
                            name="zip"
                            value="{{ old('zip', $customer->zip) }}"
                            maxlength="20"
                            class="input-field w-full"
                        />
                    </div>
                </div>

                <div class="pt-2">
                    <x-storefront.button type="submit" size="md">Save changes</x-storefront.button>
                </div>
            </form>
        </div>
    </section>
</x-layouts.storefront>
