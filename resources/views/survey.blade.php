@extends('layouts.storefront')

@section('content')
@php
    $storeName = \App\Models\Setting::get('store_name', 'Our Bakery');
@endphp

<div class="max-w-2xl mx-auto px-4 py-12">
    @if(session('survey_submitted'))
        <div class="text-center py-16">
            <div class="text-5xl mb-4">🎉</div>
            <h1 class="font-display text-3xl font-bold mb-4" style="color: var(--warm-900);">Thank You!</h1>
            <p class="text-lg" style="color: var(--warm-700);">Your feedback has been submitted. We appreciate you taking the time to share your thoughts!</p>
            <a href="{{ route('home') }}" class="btn-primary inline-block mt-8 px-6 py-3">Back to {{ $storeName }}</a>
        </div>
    @else
        <h1 class="font-display text-3xl font-bold mb-2" style="color: var(--warm-900);">{{ $survey->title }}</h1>
        @if($survey->description)
            <p class="text-lg mb-8" style="color: var(--warm-700);">{{ $survey->description }}</p>
        @endif

        <form method="POST" action="{{ route('survey.submit', $survey) }}" class="space-y-8">
            @csrf

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium mb-1" style="color: var(--warm-800);">Your Name (optional)</label>
                    <input type="text" name="customer_name" class="input-field">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1" style="color: var(--warm-800);">Your Email (optional)</label>
                    <input type="email" name="customer_email" class="input-field">
                </div>
            </div>

            @foreach($survey->questions as $index => $question)
                <div class="card p-6">
                    <label class="block font-semibold text-lg mb-3" style="color: var(--warm-900);">
                        {{ $index + 1 }}. {{ $question['question'] }}
                    </label>

                    @if($question['type'] === 'rating')
                        <div class="flex gap-2" x-data="{ rating: 0 }">
                            @for($star = 1; $star <= 5; $star++)
                                <button type="button"
                                    @click="rating = {{ $star }}"
                                    :class="rating >= {{ $star }} ? 'text-amber-400' : 'text-gray-300'"
                                    class="text-3xl hover:text-amber-400 transition-colors cursor-pointer">
                                    ★
                                </button>
                            @endfor
                            <input type="hidden" name="answers[{{ $index }}]" x-bind:value="rating">
                        </div>

                    @elseif($question['type'] === 'text')
                        <textarea name="answers[{{ $index }}]" rows="3"
                            class="input-field"
                            placeholder="Type your answer..."></textarea>

                    @elseif($question['type'] === 'multiple_choice')
                        <div class="space-y-2">
                            @foreach($question['options'] ?? [] as $option)
                                <label class="flex items-center gap-3 cursor-pointer p-2 rounded-lg hover:bg-warm-50">
                                    <input type="radio" name="answers[{{ $index }}]" value="{{ $option }}"
                                        class="w-4 h-4" style="accent-color: var(--warm-600);">
                                    <span style="color: var(--warm-800);">{{ $option }}</span>
                                </label>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endforeach

            <button type="submit" class="btn-primary px-8 py-3 text-lg w-full">
                Submit Feedback
            </button>
        </form>
    @endif
</div>
@endsection
