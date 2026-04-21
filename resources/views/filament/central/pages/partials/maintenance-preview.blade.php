<x-central.card padding="p-10" class="text-center">
    <div class="text-honey text-[1.75rem] font-bold mb-4">🍞 KneadIt</div>
    <div class="text-white text-[1.25rem] font-bold mb-6">We'll be back soon!</div>
    <div class="text-parchment max-w-[28rem] mx-auto leading-relaxed">
        {{ $message ?: 'We are currently performing scheduled maintenance. We\'ll be back shortly!' }}
    </div>
    @if ($scheduled_end)
        <div class="mt-4 text-cinnamon text-[0.85rem]">
            Expected back: {{ \Carbon\Carbon::parse($scheduled_end)->format('M j, Y g:i A') }}
        </div>
    @endif
</x-central.card>
