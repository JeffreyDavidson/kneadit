<x-filament-panels::page>
    <div class="space-y-6">
        <div class="flex items-end gap-4">
            <div class="max-w-sm flex-1">
                <label class="mb-1 block text-sm font-medium">Select Survey</label>
                <select
                    wire:model.live="surveyId"
                    class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900"
                >
                    <option value="">-- Choose a survey --</option>
                    @foreach ($surveys as $id => $title)
                        <option value="{{ $id }}">{{ $title }}</option>
                    @endforeach
                </select>
            </div>

            @if ($survey)
                <button
                    wire:click="exportCsv"
                    class="fi-btn fi-btn-size-md fi-color-primary bg-primary-600 hover:bg-primary-500 inline-flex items-center gap-1 rounded-lg px-4 py-2 text-sm font-semibold text-white"
                >
                    Export CSV
                </button>
            @endif
        </div>

        @if ($survey)
            <div class="text-sm text-gray-500">{{ $viewModel->responseCount }} responses</div>

            @if ($viewModel->responseCount === 0)
                <p class="text-gray-500 italic">No responses yet.</p>
            @else
                <div class="space-y-6">
                    @foreach ($viewModel->questionResults as $index => $result)
                        <div class="rounded-xl border bg-white p-5 shadow-sm dark:bg-gray-800">
                            <h4 class="mb-3 text-lg font-semibold">{{ $index + 1 }}. {{ $result['question'] }}</h4>

                            @if ($result['type'] === 'rating')
                                <p class="mb-3 text-2xl font-bold">
                                    {{ $result['average'] }} / 5
                                    <span class="text-sm font-normal text-gray-500">({{ $result['answerCount'] }} ratings)</span>
                                </p>
                                <div class="space-y-1">
                                    @foreach ($result['ratingDistribution'] as $rating)
                                        <div class="flex items-center gap-2 text-sm">
                                            <span class="w-8 text-right">{{ $rating['rating'] }}/5</span>
                                            <div class="h-4 flex-1 overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700">
                                                <div
                                                    class="h-full rounded-full bg-amber-500 transition-all"
                                                    style="width: {{ $rating['percentage'] }}%"
                                                ></div>
                                            </div>
                                            <span class="w-8 text-gray-500">{{ $rating['count'] }}</span>
                                        </div>
                                    @endforeach
                                </div>

                            @elseif ($result['type'] === 'multiple_choice')
                                <div class="space-y-2">
                                    @foreach ($result['choiceBreakdown'] as $choice)
                                        <div class="flex items-center gap-2 text-sm">
                                            <span class="w-40 truncate">{{ $choice['choice'] }}</span>
                                            <div class="h-4 flex-1 overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700">
                                                <div
                                                    class="h-full rounded-full bg-blue-500 transition-all"
                                                    style="width: {{ $choice['percentage'] }}%"
                                                ></div>
                                            </div>
                                            <span class="w-20 text-right text-gray-500">{{ $choice['percentage'] }}% ({{ $choice['count'] }})</span>
                                        </div>
                                    @endforeach
                                </div>

                            @elseif ($result['type'] === 'text')
                                <div class="max-h-64 space-y-2 overflow-y-auto">
                                    @foreach ($result['textAnswers'] as $text)
                                        <div class="rounded-lg bg-gray-50 p-3 text-sm dark:bg-gray-900">
                                            {{ $text }}
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        @else
            <p class="text-gray-500 italic">Select a survey to view results.</p>
        @endif
    </div>
</x-filament-panels::page>
