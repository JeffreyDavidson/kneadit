<x-filament-panels::page>
    <form wire:submit="send" class="space-y-4">
        {{ $this->form }}

        <div class="flex justify-end">
            <button type="submit" class="rounded-lg px-4 py-2 text-sm font-medium" style="background-color: #d4920c; color: #1c1410;">
                Send Message
            </button>
        </div>
    </form>
</x-filament-panels::page>
