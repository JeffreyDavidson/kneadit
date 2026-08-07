<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>{{ __('invitations.show.title', ['store' => $settings->store->name, 'app' => config('app.name')]) }}</title>
    @vite(['resources/css/storefront.css'])
</head>
<body class="flex min-h-screen items-center justify-center bg-amber-50 p-4">
    <div class="w-full max-w-md overflow-hidden rounded-2xl bg-white shadow-lg">
        <div class="bg-gradient-to-r from-amber-700 to-amber-500 p-8 text-center">
            <h1 class="text-3xl font-bold text-white">
                🍞 {{ __('invitations.show.brand', ['app' => config('app.name')]) }}
            </h1>
            <p class="mt-2 text-amber-100">{{ __('invitations.show.invited') }}</p>
        </div>
        <div class="p-8">
            <h2 class="mb-2 text-xl font-semibold text-gray-800">
                {{ __('invitations.show.join', ['store' => $settings->store->name]) }}
            </h2>
            <p class="mb-6 text-gray-600">
                {!! __('invitations.show.invited_as', ['role' => e($invitation->role->getLabel())]) !!}
            </p>

            @if ($existingUser)
                <form method="POST" action="{{ route('invitation.accept', $invitation->token) }}">
                    @csrf
                    <p class="mb-4 text-gray-600">
                        {!! __('invitations.show.welcome_back', ['name' => e($existingUser->name)]) !!}
                    </p>
                    <button
                        type="submit"
                        class="w-full rounded-lg bg-amber-600 px-6 py-3 font-semibold text-white transition hover:bg-amber-700"
                    >
                        {{ __('invitations.show.accept') }}
                    </button>
                </form>
            @else
                <form method="POST" action="{{ route('invitation.accept', $invitation->token) }}">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">{{ __('forms.labels.name') }}</label>
                            <input
                                type="text"
                                name="name"
                                value="{{ old('name') }}"
                                required
                                class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-amber-500 focus:ring-2 focus:ring-amber-500"
                            />
                            @error('name')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">{{ __('forms.labels.email') }}</label>
                            <input
                                type="email"
                                value="{{ $invitation->email }}"
                                disabled
                                class="w-full rounded-lg border border-gray-200 bg-gray-50 px-4 py-2 text-gray-500"
                            />
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">{{ __('forms.labels.password') }}</label>
                            <input
                                type="password"
                                name="password"
                                required
                                minlength="8"
                                class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-amber-500 focus:ring-2 focus:ring-amber-500"
                            />
                            @error('password')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">{{ __('forms.labels.confirm_password') }}</label>
                            <input
                                type="password"
                                name="password_confirmation"
                                required
                                class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-amber-500 focus:ring-2 focus:ring-amber-500"
                            />
                        </div>
                        <button
                            type="submit"
                            class="w-full rounded-lg bg-amber-600 px-6 py-3 font-semibold text-white transition hover:bg-amber-700"
                        >
                            {{ __('invitations.show.create_and_accept') }}
                        </button>
                    </div>
                </form>
            @endif
        </div>
    </div>
</body>
</html>
