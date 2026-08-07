@php
    $survey = $getRecord();
    $questions = $survey->questions ?? [];
    $responses = $survey->responses;
@endphp

<div class="space-y-6">
    @if ($responses->isEmpty())
        <p class="text-gray-500 italic">No responses yet.</p>
    @else
        @foreach ($questions as $index => $question)
            <div class="rounded-lg border bg-white p-4 dark:bg-gray-800">
                <h4 class="mb-3 text-lg font-semibold">{{ $index + 1 }}. {{ $question['question'] }}</h4>

                @if ($question['type'] === 'rating')
                    @php
                        $ratings = $responses->pluck('answers')->map(fn ($a) => $a[$index] ?? null)->filter()->map(fn ($v) => (int) $v);
                        $avg = $ratings->count() ? round($ratings->avg(), 1) : 0;
                        $distribution = collect(range(1, 5))->mapWithKeys(fn ($r) => [$r => $ratings->filter(fn ($v) => $v === $r)->count()]);
                        $maxCount = max($distribution->max(), 1);
                    @endphp
                    <p class="mb-2 text-2xl font-bold">
                        {{ $avg }} / 5 <span class="text-sm text-gray-500">({{ $ratings->count() }} ratings)</span>
                    </p>
                    <div class="space-y-1">
                        @foreach ($distribution->reverse() as $star => $count)
                            <x-admin.distribution-bar
                                :label="$star . '/5'"
                                :percentage="($count / $maxCount) * 100"
                                :count="$count"
                            />
                        @endforeach
                    </div>

                @elseif ($question['type'] === 'multiple_choice')
                    @php
                        $choices = $responses->pluck('answers')->map(fn ($a) => $a[$index] ?? null)->filter();
                        $total = $choices->count() ?: 1;
                        $breakdown = $choices->countBy()->sortDesc();
                    @endphp
                    <div class="space-y-2">
                        @foreach ($breakdown as $option => $count)
                            <x-admin.distribution-bar
                                :label="$option"
                                :percentage="($count / $total) * 100"
                                :count="$count"
                                color="bg-blue-500"
                                label-width="w-40"
                                label-align="truncate"
                                count-width="w-16"
                                count-align="text-right"
                                :count-suffix="round(($count / $total) * 100) . '% (' . $count . ')'"
                            />
                        @endforeach
                    </div>

                @elseif ($question['type'] === 'text')
                    @php
                        $texts = $responses->pluck('answers')->map(fn ($a) => $a[$index] ?? null)->filter();
                    @endphp
                    <div class="max-h-48 space-y-2 overflow-y-auto">
                        @foreach ($texts as $text)
                            <div class="rounded bg-gray-50 p-2 text-sm dark:bg-gray-900">{{ $text }}</div>
                        @endforeach
                    </div>
                @endif
            </div>
        @endforeach
    @endif
</div>
