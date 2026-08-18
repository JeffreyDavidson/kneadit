<x-filament-panels::page>
    <div class="space-y-4">
        <p class="text-sm text-gray-500 dark:text-gray-400">
            Customize the subject line and body content of emails sent to your customers. Use placeholders like
            <code class="text-primary-600 dark:text-primary-400">{customer_name}</code> to insert dynamic values.
        </p>

        @foreach ($this->getTemplateData() as $template)
            <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <div class="flex items-center justify-between p-4">
                    <div class="flex-1">
                        <div class="flex items-center gap-3">
                            <h3 class="text-sm font-medium text-gray-950 dark:text-white">{{ $template['label'] }}</h3>
                            @if ($template['status'] === 'Customized')
                                <span class="bg-success-50 text-success-700 ring-success-600/20 dark:bg-success-400/10 dark:text-success-400 dark:ring-success-400/30 inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset">
                                    Customized
                                </span>
                            @else
                                <span class="inline-flex items-center rounded-md bg-gray-50 px-2 py-1 text-xs font-medium text-gray-600 ring-1 ring-gray-500/10 ring-inset dark:bg-gray-400/10 dark:text-gray-400 dark:ring-gray-400/20">
                                    Default
                                </span>
                            @endif
                        </div>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $template['description'] }}</p>
                    </div>
                    <div class="flex items-center gap-2">
                        @if ($template['status'] === 'Customized')
                            <x-filament::button
                                size="sm"
                                color="gray"
                                wire:click="resetTemplate('{{ $template['type'] }}')"
                                wire:confirm="Are you sure you want to reset this template to the default?"
                            >
                                Reset
                            </x-filament::button>
                        @endif
                        <x-filament::button size="sm" wire:click="editTemplate('{{ $template['type'] }}')">
                            Edit
                        </x-filament::button>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <x-filament-actions::modals />
</x-filament-panels::page>
