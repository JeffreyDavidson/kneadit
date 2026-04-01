<x-filament-panels::page>
    <div class="space-y-6">
        <div class="flex items-end gap-4">
            <div class="flex-1 max-w-sm">
                <label class="block text-sm font-medium mb-1">Select Survey</label>
                <select wire:model.live="surveyId" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900">
                    <option value="">-- Choose a survey --</option>
                    @foreach ($surveys as $id => $title)
                        <option value="{{ $id }}">{{ $title }}</option>
                    @endforeach
                </select>
            </div>

            @if ($survey)
                <button wire:click="exportCsv" class="fi-btn fi-btn-size-md fi-color-primary inline-flex items-center gap-1 rounded-lg px-4 py-2 text-sm font-semibold bg-primary-600 text-white hover:bg-primary-500">
                    Export CSV
                </button>
            @endif
        </div>

        @if ($survey)
            @php
                $questions = $survey->questions ?? [];
                $responses = $survey->responses;
            @endphp

            <div class="text-sm text-gray-500">{{ $responses->count() }} responses</div>

            @if ($responses->isEmpty())
                <p class="text-gray-500 italic">No responses yet.</p>
            @else
                <div class="space-y-6">
                    @foreach ($questions as $index => $question)
                        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border p-5">
                            <h4 class="font-semibold text-lg mb-3">{{ $index + 1 }}. {{ $question['question'] }}</h4>

                            @if ($question['type'] === 'rating')
                                @php
                                    $ratings = $responses->pluck('answers')->map(fn($a) => $a[$index] ?? null)->filter()->map(fn($v) => (int)$v);
                                    $avg = $ratings->count() ? round($ratings->avg(), 1) : 0;
                                    $distribution = collect(range(1, 5))->mapWithKeys(fn($r) => [$r => $ratings->filter(fn($v) => $v === $r)->count()]);
                                    $maxCount = max($distribution->max(), 1);
                                @endphp
                                <p class="text-2xl font-bold mb-3">{{ $avg }} / 5 <span class="text-sm font-normal text-gray-500">({{ $ratings->count() }} ratings)</span></p>
                                <div class="space-y-1">
                                    @foreach ($distribution->reverse() as $star => $count)
                                        <div class="flex items-center gap-2 text-sm">
                                            <span class="w-8 text-right">{{ $star }}★</span>
                                            <div class="flex-1 bg-gray-200 dark:bg-gray-700 rounded-full h-4 overflow-hidden">
                                                <div class="bg-amber-500 h-full rounded-full transition-all" style="width: {{ ($count / $maxCount) * 100 }}%"></div>
                                            </div>
                                            <span class="w-8 text-gray-500">{{ $count }}</span>
                                        </div>
                                    @endforeach
                                </div>

                            @elseif ($question['type'] === 'multiple_choice')
                                @php
                                    $choices = $responses->pluck('answers')->map(fn($a) => $a[$index] ?? null)->filter();
                                    $total = $choices->count() ?: 1;
                                    $breakdown = $choices->countBy()->sortDesc();
                                @endphp
                                <div class="space-y-2">
                                    @foreach ($breakdown as $option => $count)
                                        <div class="flex items-center gap-2 text-sm">
                                            <span class="w-40 truncate">{{ $option }}</span>
                                            <div class="flex-1 bg-gray-200 dark:bg-gray-700 rounded-full h-4 overflow-hidden">
                                                <div class="bg-blue-500 h-full rounded-full transition-all" style="width: {{ ($count / $total) * 100 }}%"></div>
                                            </div>
                                            <span class="w-20 text-gray-500 text-right">{{ round(($count / $total) * 100) }}% ({{ $count }})</span>
                                        </div>
                                    @endforeach
                                </div>

                            @elseif ($question['type'] === 'text')
                                @php
                                    $texts = $responses->pluck('answers')->map(fn($a) => $a[$index] ?? null)->filter();
                                @endphp
                                <div class="max-h-64 overflow-y-auto space-y-2">
                                    @foreach ($texts as $text)
                                        <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-3 text-sm">{{ $text }}</div>
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
