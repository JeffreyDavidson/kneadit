<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('invitations.show.title', ['store' => $settings->storeName, 'app' => config('app.name')]) }}</title>
    @vite(["resources/css/storefront.css"])
</head>
<body class="min-h-screen bg-amber-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-lg max-w-md w-full overflow-hidden">
        <div class="bg-gradient-to-r from-amber-700 to-amber-500 p-8 text-center">
            <h1 class="text-3xl font-bold text-white">🍞 {{ __('invitations.show.brand', ['app' => config('app.name')]) }}</h1>
            <p class="text-amber-100 mt-2">{{ __('invitations.show.invited') }}</p>
        </div>
        <div class="p-8">
            <h2 class="text-xl font-semibold text-gray-800 mb-2">{{ __('invitations.show.join', ['store' => $settings->storeName]) }}</h2>
            <p class="text-gray-600 mb-6">
                {!! __('invitations.show.invited_as', ['role' => e($invitation->role->getLabel())]) !!}
            </p>

            @if ($existingUser)
                <form method="POST" action="{{ route('invitation.accept', $invitation->token) }}">
                    @csrf
                    <p class="text-gray-600 mb-4">
                        {!! __('invitations.show.welcome_back', ['name' => e($existingUser->name)]) !!}
                    </p>
                    <button type="submit" class="w-full bg-amber-600 hover:bg-amber-700 text-white font-semibold py-3 px-6 rounded-lg transition">
                        {{ __('invitations.show.accept') }}
                    </button>
                </form>
            @else
                <form method="POST" action="{{ route('invitation.accept', $invitation->token) }}">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('forms.labels.name') }}</label>
                            <input type="text" name="name" value="{{ old('name') }}" required
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
                            @error('name') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('forms.labels.email') }}</label>
                            <input type="email" value="{{ $invitation->email }}" disabled
                                class="w-full border border-gray-200 rounded-lg px-4 py-2 bg-gray-50 text-gray-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('forms.labels.password') }}</label>
                            <input type="password" name="password" required minlength="8"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
                            @error('password') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('forms.labels.confirm_password') }}</label>
                            <input type="password" name="password_confirmation" required
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
                        </div>
                        <button type="submit" class="w-full bg-amber-600 hover:bg-amber-700 text-white font-semibold py-3 px-6 rounded-lg transition">
                            {{ __('invitations.show.create_and_accept') }}
                        </button>
                    </div>
                </form>
            @endif
        </div>
    </div>
</body>
</html>
