<div class="rounded-lg border-2 border-amber-500/30 bg-gray-900 p-8 text-center">
    <div class="mb-4 text-3xl font-bold text-amber-400">🍞 KneadIt</div>
    <div class="mb-6 text-xl font-semibold text-amber-300">We'll be back soon!</div>
    <p class="mx-auto max-w-md text-gray-300">
        {{ $message ?: 'We are currently performing scheduled maintenance. We\'ll be back shortly!' }}
    </p>
    @if($scheduled_end)
        <p class="mt-4 text-sm text-amber-400/80">
            Expected back: {{ \Carbon\Carbon::parse($scheduled_end)->format('M j, Y g:i A') }}
        </p>
    @endif
</div>
