<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Join {{ $storeName }} on KneadIt</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-amber-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-lg max-w-md w-full overflow-hidden">
        <div class="bg-gradient-to-r from-amber-700 to-amber-500 p-8 text-center">
            <h1 class="text-3xl font-bold text-white">🍞 KneadIt</h1>
            <p class="text-amber-100 mt-2">You've been invited!</p>
        </div>
        <div class="p-8">
            <h2 class="text-xl font-semibold text-gray-800 mb-2">Join {{ $storeName }}</h2>
            <p class="text-gray-600 mb-6">
                You've been invited as a <strong class="text-amber-700">{{ ucfirst($invitation->role) }}</strong>.
            </p>

            @if($existingUser)
                <form method="POST" action="{{ route('invitation.accept', $invitation->token) }}">
                    @csrf
                    <p class="text-gray-600 mb-4">
                        Welcome back, <strong>{{ $existingUser->name }}</strong>! Click below to accept.
                    </p>
                    <button type="submit" class="w-full bg-amber-600 hover:bg-amber-700 text-white font-semibold py-3 px-6 rounded-lg transition">
                        Accept Invitation
                    </button>
                </form>
            @else
                <form method="POST" action="{{ route('invitation.accept', $invitation->token) }}">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                            <input type="text" name="name" value="{{ old('name') }}" required
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
                            @error('name') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <input type="email" value="{{ $invitation->email }}" disabled
                                class="w-full border border-gray-200 rounded-lg px-4 py-2 bg-gray-50 text-gray-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                            <input type="password" name="password" required minlength="8"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
                            @error('password') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
                            <input type="password" name="password_confirmation" required
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
                        </div>
                        <button type="submit" class="w-full bg-amber-600 hover:bg-amber-700 text-white font-semibold py-3 px-6 rounded-lg transition">
                            Create Account & Accept
                        </button>
                    </div>
                </form>
            @endif
        </div>
    </div>
</body>
</html>
