@php
/** @var \App\Models\Customers\Customer $customer */
@endphp
<x-layouts.storefront>
    <section class="max-w-2xl mx-auto px-4 py-12">
        <div class="flex items-center justify-between mb-8">
            <h1 class="font-display text-3xl text-warm-900">Your profile</h1>
            <a href="{{ route('account.dashboard') }}" class="text-sm font-semibold text-warm-700 hover:underline">
                &larr; Back to dashboard
            </a>
        </div>

        @if (session('status'))
            <div class="mb-6 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-800">
                {{ session('status') }}
            </div>
        @endif

        <div class="card p-6">
            <p class="text-sm text-warm-500 mb-6">
                Email: <strong class="text-warm-700">{{ $customer->email }}</strong>
                <span class="text-xs text-warm-500">— to change your email, please contact us.</span>
            </p>

            <form method="POST" action="{{ route('account.profile.update') }}" class="space-y-5">
                @csrf

                <div>
                    <label for="profile-name" class="block text-sm font-semibold text-warm-700 mb-1">Name</label>
                    <input id="profile-name" type="text" name="name" value="{{ old('name', $customer->name) }}"
                           required maxlength="255" class="input-field w-full" />
                    @error('name')<p class="text-sm text-red-700 mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="profile-phone" class="block text-sm font-semibold text-warm-700 mb-1">Phone</label>
                    <input id="profile-phone" type="tel" name="phone" value="{{ old('phone', $customer->phone) }}"
                           maxlength="20" class="input-field w-full" />
                    @error('phone')<p class="text-sm text-red-700 mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="profile-birthday" class="block text-sm font-semibold text-warm-700 mb-1">Birthday</label>
                    <input id="profile-birthday" type="date" name="birthday"
                           value="{{ old('birthday', optional($customer->birthday)->format('Y-m-d')) }}"
                           class="input-field w-full" />
                    @error('birthday')<p class="text-sm text-red-700 mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="profile-address" class="block text-sm font-semibold text-warm-700 mb-1">Address</label>
                    <input id="profile-address" type="text" name="address" value="{{ old('address', $customer->address) }}"
                           maxlength="255" class="input-field w-full" />
                    @error('address')<p class="text-sm text-red-700 mt-1">{{ $message }}</p>@enderror
                </div>

                <div class="grid sm:grid-cols-3 gap-3">
                    <div>
                        <label for="profile-city" class="block text-sm font-semibold text-warm-700 mb-1">City</label>
                        <input id="profile-city" type="text" name="city" value="{{ old('city', $customer->city) }}"
                               maxlength="100" class="input-field w-full" />
                    </div>
                    <div>
                        <label for="profile-state" class="block text-sm font-semibold text-warm-700 mb-1">State</label>
                        <input id="profile-state" type="text" name="state" value="{{ old('state', $customer->state) }}"
                               maxlength="100" class="input-field w-full" />
                    </div>
                    <div>
                        <label for="profile-zip" class="block text-sm font-semibold text-warm-700 mb-1">Zip</label>
                        <input id="profile-zip" type="text" name="zip" value="{{ old('zip', $customer->zip) }}"
                               maxlength="20" class="input-field w-full" />
                    </div>
                </div>

                <div class="pt-2">
                    <x-storefront.button type="submit" size="md">Save changes</x-storefront.button>
                </div>
            </form>
        </div>
    </section>
</x-layouts.storefront>
