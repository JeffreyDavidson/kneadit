@extends('layouts.storefront')

@php
    $storeName = settings('store_name', 'Our Bakery');
    $heroImage = settings('hero_image');
    $heroImageUrl = $heroImage ? Storage::url($heroImage) : 'https://images.unsplash.com/photo-1509440159596-0249088772ff?w=1920&q=80';
@endphp

@section('content')
@php
    $content = settingsPageContent('survey');
@endphp
@if(session('survey_submitted'))
{{-- Success State --}}
<section class="relative overflow-hidden" style="min-height: 60vh;">
    <div class="absolute inset-0">
        <img src="{{ $heroImageUrl }}" alt="Survey submitted" class="w-full h-full object-cover survey-hero-img">
    </div>
    <div class="absolute inset-0" style="background: linear-gradient(to bottom, rgba(28,20,16,0.4) 0%, rgba(28,20,16,0.65) 50%, rgba(28,20,16,0.95) 100%);"></div>
    <div class="absolute inset-0 opacity-[0.03]" style="background-image: url('data:image/svg+xml,%3Csvg viewBox=%220 0 256 256%22 xmlns=%22http://www.w3.org/2000/svg%22%3E%3Cfilter id=%22noise%22%3E%3CfeTurbulence type=%22fractalNoise%22 baseFrequency=%220.9%22 numOctaves=%224%22 stitchTiles=%22stitch%22/%3E%3C/filter%3E%3Crect width=%22100%25%22 height=%22100%25%22 filter=%22url(%23noise)%22/%3E%3C/svg%3E');"></div>

    <div class="relative z-10 max-w-lg mx-auto text-center px-4 py-28">
        <div class="w-20 h-20 rounded-full mx-auto mb-6 flex items-center justify-center survey-fade-up" style="background: rgba(212,146,12,0.15); border: 2px solid var(--warm-500); animation-delay: 0.3s;">
            <svg class="w-10 h-10" style="color: var(--warm-500);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
            </svg>
        </div>
        <h1 class="font-display text-4xl font-bold mb-4 survey-fade-up" style="color: var(--warm-100); animation-delay: 0.5s;">{{ $content['success_title'] ?? 'Thank You!' }}</h1>
        <p class="text-lg mb-10 survey-fade-up" style="color: var(--warm-400); animation-delay: 0.7s;">{{ $content['success_description'] ?? 'Your feedback has been submitted. We appreciate you taking the time to share your thoughts!' }}</p>
        <a href="{{ route('home') }}" class="inline-block px-10 py-4 rounded-full font-semibold text-lg transition-all duration-300 hover:scale-105 survey-fade-up" style="background: var(--warm-500); color: var(--warm-900); animation-delay: 0.9s;">
            Back to {{ $storeName }}
        </a>
    </div>
</section>
@else

{{-- Photo-Forward Hero --}}
<section class="relative overflow-hidden" style="min-height: 40vh;">
    <div class="absolute inset-0">
        <img src="{{ $heroImageUrl }}" alt="Share your feedback" class="w-full h-full object-cover survey-hero-img">
    </div>
    <div class="absolute inset-0" style="background: linear-gradient(to bottom, rgba(28,20,16,0.4) 0%, rgba(28,20,16,0.65) 50%, rgba(28,20,16,0.95) 100%);"></div>
    <div class="absolute inset-0 opacity-[0.03]" style="background-image: url('data:image/svg+xml,%3Csvg viewBox=%220 0 256 256%22 xmlns=%22http://www.w3.org/2000/svg%22%3E%3Cfilter id=%22noise%22%3E%3CfeTurbulence type=%22fractalNoise%22 baseFrequency=%220.9%22 numOctaves=%224%22 stitchTiles=%22stitch%22/%3E%3C/filter%3E%3Crect width=%22100%25%22 height=%22100%25%22 filter=%22url(%23noise)%22/%3E%3C/svg%3E');"></div>

    <div class="relative z-10 max-w-3xl mx-auto text-center px-4 py-20 md:py-24">
        <div class="flex items-center justify-center gap-3 mb-6 survey-fade-up" style="animation-delay: 0.3s;">
            <span class="block w-8 h-px" style="background: var(--warm-500);"></span>
            <span class="uppercase tracking-[0.25em] text-xs font-semibold" style="color: var(--warm-500);">{{ $content['hero_eyebrow'] ?? 'Your Opinion Matters' }}</span>
            <span class="block w-8 h-px" style="background: var(--warm-500);"></span>
        </div>
        <h1 class="font-display text-3xl md:text-5xl font-bold mb-4 leading-tight survey-fade-up" style="color: var(--warm-100); animation-delay: 0.5s;">{{ $survey->title }}</h1>
        @if($survey->description)
        <p class="font-script text-2xl md:text-3xl survey-fade-up" style="color: var(--warm-400); animation-delay: 0.7s;">{{ $survey->description }}</p>
        @endif
    </div>
</section>

{{-- Survey Form --}}
<section class="py-16 px-4" style="background: var(--warm-50);">
    <div class="max-w-2xl mx-auto" x-data="{ totalQuestions: {{ count($survey->questions) + 1 }} }">

        {{-- Progress Bar --}}
        <div class="mb-10">
            <div class="flex justify-between items-center mb-2">
                <span class="text-sm font-semibold" style="color: var(--warm-600);">{{ count($survey->questions) }} questions</span>
                <span class="text-sm" style="color: var(--warm-500);">Takes about {{ max(1, ceil(count($survey->questions) * 0.5)) }} min</span>
            </div>
            <div class="w-full rounded-full h-2" style="background: var(--warm-200);">
                <div class="h-full rounded-full" style="background: linear-gradient(90deg, var(--warm-500), var(--warm-400)); width: 0%;" id="surveyProgress"></div>
            </div>
        </div>

        <form method="POST" action="{{ route('survey.submit', $survey) }}" class="space-y-6">
            @csrf

            {{-- Contact Info --}}
            <div class="rounded-2xl p-6 md:p-8" style="background: white; border: 1px solid var(--warm-200);">
                <p class="font-script text-2xl md:text-3xl mb-4" style="color: var(--warm-400);">A little about you</p>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-semibold mb-1" style="color: var(--warm-700);">Your Name (optional)</label>
                        <input type="text" name="customer_name" class="input-field">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-1" style="color: var(--warm-700);">Your Email (optional)</label>
                        <input type="email" name="customer_email" class="input-field">
                    </div>
                </div>
            </div>

            {{-- Questions --}}
            @foreach($survey->questions as $index => $question)
            <div class="rounded-2xl p-6 md:p-8 transition-all duration-300 hover:shadow-lg" style="background: white; border: 1px solid var(--warm-200);">
                <div class="flex items-start gap-4 mb-4">
                    <span class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold" style="background: var(--warm-500); color: var(--warm-900);">{{ $index + 1 }}</span>
                    <label class="font-display text-lg font-semibold leading-snug" style="color: var(--warm-900);">
                        {{ $question['question'] }}
                    </label>
                </div>

                <div class="pl-12">
                    @if($question['type'] === 'rating')
                    <div x-data="{ rating: 0 }" class="flex gap-2 items-center">
                        @for($star = 1; $star <= 5; $star++)
                        <button type="button"
                            @click="rating = {{ $star }}"
                            :class="rating >= {{ $star }} ? '' : 'opacity-30'"
                            :style="rating >= {{ $star }} ? 'color: var(--warm-500)' : 'color: var(--warm-400)'"
                            class="text-4xl transition-all duration-200 hover:scale-125 cursor-pointer">
                            ★
                        </button>
                        @endfor
                        <input type="hidden" name="answers[{{ $index }}]" x-bind:value="rating">
                        <span class="ml-3 text-sm font-semibold" style="color: var(--warm-500);" x-show="rating > 0" x-text="['', 'Poor', 'Fair', 'Good', 'Great', 'Excellent'][rating]"></span>
                    </div>

                    @elseif($question['type'] === 'text')
                    <textarea name="answers[{{ $index }}]" rows="3" class="input-field" placeholder="Type your answer..."></textarea>

                    @elseif($question['type'] === 'multiple_choice')
                    <div class="space-y-2">
                        @foreach($question['options'] ?? [] as $option)
                        <label class="flex items-center gap-3 cursor-pointer p-3 rounded-xl transition-all duration-200 hover:shadow-sm" style="border: 1px solid var(--warm-200);" onmouseover="this.style.borderColor='var(--warm-400)'" onmouseout="this.style.borderColor='var(--warm-200)'">
                            <input type="radio" name="answers[{{ $index }}]" value="{{ $option }}"
                                class="w-4 h-4" style="accent-color: var(--warm-500);">
                            <span class="font-medium" style="color: var(--warm-800);">{{ $option }}</span>
                        </label>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>
            @endforeach

            {{-- Submit --}}
            <div class="text-center pt-4">
                <button type="submit" class="px-12 py-4 rounded-full font-bold text-lg transition-all duration-300 hover:scale-105 hover:shadow-2xl" style="background: var(--warm-500); color: var(--warm-900);">
                    {{ $content['submit_button'] ?? 'Submit Feedback' }}
                </button>
                <p class="text-sm mt-4" style="color: var(--warm-500);">{{ $content['submit_footer'] ?? 'Your feedback helps us bake better for you' }}</p>
            </div>
        </form>
    </div>
</section>
@endif
@endsection

@section('styles')
<link rel="stylesheet" href="{{ asset('css/survey.css') }}">
@endsection