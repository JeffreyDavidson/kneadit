@use(App\Enums\Engagement\SurveyQuestionType)
<x-layouts.storefront>

@if (session('survey_submitted'))
{{-- Success State --}}
<x-storefront.hero-section :image="$settings->heroImageUrl()" image-alt="Survey submitted" image-class="hero-img" min-height="60vh">

    <div class="relative z-10 max-w-lg mx-auto text-center px-4 py-28">
        <div class="w-20 h-20 rounded-full mx-auto mb-6 flex items-center justify-center hero-fade-up" style="background: rgba(212,146,12,0.15); border: 2px solid var(--warm-500); animation-delay: 0.3s;">
            <x-heroicon-o-check class="w-10 h-10 text-warm-500" stroke-width="2.5" />
        </div>
        <h1 class="font-display text-4xl font-bold mb-4 hero-fade-up" style="color: var(--warm-100); animation-delay: 0.5s;">{{ $content['success_title'] ?? 'Thank You!' }}</h1>
        <p class="text-lg mb-10 hero-fade-up" style="color: var(--warm-400); animation-delay: 0.7s;">{{ $content['success_description'] ?? 'Your feedback has been submitted. We appreciate you taking the time to share your thoughts!' }}</p>
        <a href="{{ route('home') }}" class="inline-block px-10 py-4 rounded-full font-semibold text-lg transition-all duration-300 hover:scale-105 hero-fade-up" style="background: var(--warm-500); color: var(--warm-900); animation-delay: 0.9s;">
            Back to {{ $settings->store->name }}
        </a>
    </div>
</x-storefront.hero-section>
@else

{{-- Photo-Forward Hero --}}
<x-storefront.hero-section :image="$settings->heroImageUrl()" image-alt="Share your feedback" image-class="hero-img" min-height="40vh">

    <div class="relative z-10 max-w-3xl mx-auto text-center px-4 py-20 md:py-24">
        <x-storefront.eyebrow class="hero-fade-up mb-6" style="animation-delay: 0.3s;">{{ $content['hero_eyebrow'] ?? 'Your Opinion Matters' }}</x-storefront.eyebrow>
        <h1 class="font-display text-3xl md:text-5xl font-bold mb-4 leading-tight hero-fade-up" style="color: var(--warm-100); animation-delay: 0.5s;">{{ $survey->title }}</h1>
        @if ($survey->description)
        <p class="font-script text-2xl md:text-3xl hero-fade-up" style="color: var(--warm-400); animation-delay: 0.7s;">{{ $survey->description }}</p>
        @endif
    </div>
</x-storefront.hero-section>

{{-- Survey Form --}}
<section class="py-16 px-4 bg-warm-50">
    <div class="max-w-2xl mx-auto" x-data="{ totalQuestions: {{ count($survey->questions) + 1 }} }">

        {{-- Progress Bar --}}
        <div class="mb-10">
            <div class="flex justify-between items-center mb-2">
                <span class="text-sm font-semibold text-warm-600">{{ count($survey->questions) }} questions</span>
                <span class="text-sm text-warm-500">Takes about {{ max(1, ceil(count($survey->questions) * 0.5)) }} min</span>
            </div>
            <div class="w-full rounded-full h-2 bg-warm-200">
                <div class="h-full rounded-full" style="background: linear-gradient(90deg, var(--warm-500), var(--warm-400)); width: 0%;" id="surveyProgress"></div>
            </div>
        </div>

        <form method="POST" action="{{ route('survey.submit', $survey) }}" class="space-y-6">
            @csrf

            {{-- Contact Info --}}
            <div class="rounded-2xl p-6 md:p-8 bg-white border border-warm-200">
                <p class="font-script text-2xl md:text-3xl mb-4 text-warm-400">A little about you</p>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-semibold mb-1 text-warm-700">Your Name (optional)</label>
                        <input type="text" name="customer_name" class="input-field">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-1 text-warm-700">Your Email (optional)</label>
                        <input type="email" name="customer_email" class="input-field">
                    </div>
                </div>
            </div>

            {{-- Questions --}}
            @foreach ($survey->questions as $index => $question)
            <div class="rounded-2xl p-6 md:p-8 transition-all duration-300 hover:shadow-lg bg-white border border-warm-200">
                <div class="flex items-start gap-4 mb-4">
                    <span class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold bg-warm-500 text-warm-900">{{ $index + 1 }}</span>
                    <label class="font-display text-lg font-semibold leading-snug text-warm-900">
                        {{ $question['question'] }}
                    </label>
                </div>

                <div class="pl-12">
                    @if ($question['type'] === SurveyQuestionType::Rating->value)
                    <div x-data="{ rating: 0 }" class="flex gap-2 items-center">
                        @for ($star = 1; $star <= 5; $star++)
                        <button type="button"
                            @click="rating = {{ $star }}"
                            :class="rating >= {{ $star }} ? '' : 'opacity-30'"
                            :style="rating >= {{ $star }} ? 'color: var(--warm-500)' : 'color: var(--warm-400)'"
                            class="text-4xl transition-all duration-200 hover:scale-125 cursor-pointer">
                            ★
                        </button>
                        @endfor
                        <input type="hidden" name="answers[{{ $index }}]" x-bind:value="rating">
                        <span class="ml-3 text-sm font-semibold text-warm-500" x-show="rating > 0" x-text="['', 'Poor', 'Fair', 'Good', 'Great', 'Excellent'][rating]"></span>
                    </div>

                    @elseif ($question['type'] === SurveyQuestionType::Text->value)
                    <textarea name="answers[{{ $index }}]" rows="3" class="input-field" placeholder="Type your answer..."></textarea>

                    @elseif ($question['type'] === SurveyQuestionType::MultipleChoice->value)
                    <div class="space-y-2">
                        @foreach ($question['options'] ?? [] as $option)
                        <label class="flex items-center gap-3 cursor-pointer p-3 rounded-xl transition-all duration-200 hover:shadow-sm" style="border: 1px solid var(--warm-200);" onmouseover="this.style.borderColor='var(--warm-400)'" onmouseout="this.style.borderColor='var(--warm-200)'">
                            <input type="radio" name="answers[{{ $index }}]" value="{{ $option }}"
                                class="w-4 h-4" style="accent-color: var(--warm-500);">
                            <span class="font-medium text-warm-800">{{ $option }}</span>
                        </label>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>
            @endforeach

            {{-- Submit --}}
            <div class="text-center pt-4">
                <button type="submit" class="px-12 py-4 rounded-full font-bold text-lg transition-all duration-300 hover:scale-105 hover:shadow-2xl bg-warm-500 text-warm-900">
                    {{ $content['submit_button'] ?? 'Submit Feedback' }}
                </button>
                <p class="text-sm mt-4 text-warm-500">{{ $content['submit_footer'] ?? 'Your feedback helps us bake better for you' }}</p>
            </div>
        </form>
    </div>
</section>
@endif
</x-layouts.storefront>