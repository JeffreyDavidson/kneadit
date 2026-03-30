<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Header -->
        <div class="text-center">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                Instagram Caption Generator
            </h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">
                Generate engaging Instagram captions for your bakery products with custom hooks and hashtags.
            </p>
        </div>

        <!-- Form -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
            <form wire:submit="generateCaptions">
                {{ $this->form }}

                <div class="mt-6 flex justify-center">
                    <x-filament::button type="submit" icon="heroicon-o-sparkles" size="lg">Generate Captions</x-filament::button>
                </div>
            </form>
        </div>

        <!-- Generated Captions -->
        @if (!empty($captions))
            <div class="space-y-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                    Generated Captions
                </h3>

                @foreach ($captions as $index => $caption)
                    <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6 border border-gray-200 dark:border-gray-700">
                        <div class="flex justify-between items-start mb-3">
                            <h4 class="text-md font-medium text-gray-800 dark:text-gray-200">
                                Caption Variation {{ $caption['variation'] }}
                            </h4>
                            <button
                                type="button"
                                onclick="copyToClipboard('caption-{{ $index }}')"
                                class="inline-flex items-center gap-2 px-3 py-1.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-md transition-colors"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                </svg>
                                Copy
                            </button>
                        </div>

                        <div
                            id="caption-{{ $index }}"
                            class="bg-gray-50 dark:bg-gray-900 p-4 rounded-md border border-gray-200 dark:border-gray-600"
                        >
                            <pre class="whitespace-pre-wrap text-sm text-gray-700 dark:text-gray-300 font-mono leading-relaxed">{{ $caption['text'] }}</pre>
                        </div>

                        <div class="mt-3 text-xs text-gray-500 dark:text-gray-400">
                            Character count: {{ strlen($caption['text']) }}
                        </div>
                    </div>
                @endforeach

                <!-- Tips -->
                <div class="bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-800 rounded-lg p-4 mt-6">
                    <div class="flex items-start">
                        <svg class="w-5 h-5 text-blue-500 mt-0.5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                        </svg>
                        <div>
                            <h4 class="text-sm font-medium text-blue-800 dark:text-blue-200">
                                Instagram Tips
                            </h4>
                            <div class="mt-2 text-sm text-blue-700 dark:text-blue-300">
                                <ul class="list-disc list-inside space-y-1">
                                    <li>Instagram captions can be up to 2,200 characters</li>
                                    <li>The first 125 characters are shown before "more" link</li>
                                    <li>Use 5-10 hashtags for optimal reach</li>
                                    <li>Post consistently for better engagement</li>
                                    <li>Add your location to increase local discovery</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <script>
        function copyToClipboard(elementId) {
            const element = document.getElementById(elementId);
            const text = element.textContent;

            if (navigator.clipboard) {
                navigator.clipboard.writeText(text).then(function() {
                    // Show success message
                    const button = element.nextElementSibling?.querySelector('button') ||
                                 element.parentElement.querySelector('button');
                    if (button) {
                        const originalText = button.innerHTML;
                        button.innerHTML = `
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Copied!
                        `;
                        button.classList.remove('bg-primary-600', 'hover:bg-primary-700');
                        button.classList.add('bg-green-600', 'hover:bg-green-700');

                        setTimeout(function() {
                            button.innerHTML = originalText;
                            button.classList.remove('bg-green-600', 'hover:bg-green-700');
                            button.classList.add('bg-primary-600', 'hover:bg-primary-700');
                        }, 2000);
                    }
                });
            } else {
                // Fallback for older browsers
                const textArea = document.createElement('textarea');
                textArea.value = text;
                document.body.appendChild(textArea);
                textArea.select();
                document.execCommand('copy');
                document.body.removeChild(textArea);
                alert('Caption copied to clipboard!');
            }
        }
    </script>
</x-filament-panels::page>