@php
    $inputId = "hero-upload-{$image->value}";
    $previewUrl = $this->heroImagePreviewUrl($image);
@endphp

<div>
    <label for="{{ $inputId }}" class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">
        {{ $image->getLabel() }}
    </label>
    <div class="flex items-center gap-4">
        @if ($previewUrl)
            <img
                src="{{ $previewUrl }}"
                alt="{{ $image->getLabel() }} preview"
                class="h-16 w-28 rounded-lg object-cover ring-1 ring-gray-950/10 dark:ring-white/10"
            />
        @endif

        <div class="min-w-0 flex-1">
            <input
                id="{{ $inputId }}"
                type="file"
                accept="image/jpeg,image/png,image/webp"
                wire:model="heroUploads.{{ $image->value }}"
                class="block w-full text-sm text-gray-700 dark:text-gray-300"
            />
            <p
                wire:loading
                wire:target="heroUploads.{{ $image->value }}"
                class="mt-1 text-xs text-gray-500 dark:text-gray-400"
            >
                Uploading...
            </p>
            @error("heroUploads.{$image->value}")
                <p class="text-danger-600 mt-1 text-xs">{{ $message }}</p>
            @enderror

            @if ($this->hasStoredHeroImage($image))
                <button
                    type="button"
                    wire:click="removeHeroImage('{{ $image->value }}')"
                    wire:confirm="Remove this image? The storefront will go back to the default photo."
                    class="text-danger-600 hover:text-danger-500 mt-1 text-xs font-medium"
                >
                    Remove image
                </button>
            @endif
        </div>
    </div>
</div>
