@use(App\Enums\Engagement\SurveyQuestionType)
<x-layouts.storefront>
    @if (session('survey_submitted'))
        {{-- Success State --}}
        <x-storefront.hero-section
            :image="$settings->heroImageUrl()"
            image-alt="Survey submitted"
            image-class="hero-img"
            min-height="60vh"
        >
            <div class="relative z-10 mx-auto max-w-lg px-4 py-28 text-center">
                <x-storefront.icon-circle size="lg" variant="bold" class="hero-fade-1 mx-auto mb-6">
                    <x-heroicon-o-check class="text-warm-500 h-10 w-10" stroke-width="2.5" />
                </x-storefront.icon-circle>
                <h1 class="font-display hero-fade-2 text-warm-100 mb-4 text-4xl font-bold">
                    {{ $content['success_title'] ?? 'Thank You!' }}
                </h1>
                <p class="hero-fade-3 text-warm-100 mb-10 text-lg">
                    {{ $content['success_description'] ?? 'Your feedback has been submitted. We appreciate you taking the time to share your thoughts!' }}
                </p>
                <x-storefront.button :href="route('home')" size="lg" class="hero-fade-4">
                    Back to {{ $settings->store->name }}
                </x-storefront.button>
            </div>
        </x-storefront.hero-section>
    @else
        {{-- Photo-Forward Hero --}}
        <x-storefront.hero-section
            :image="$settings->heroImageUrl()"
            image-alt="Share your feedback"
            image-class="hero-img"
            min-height="40vh"
        >
            <div class="relative z-10 mx-auto max-w-3xl px-4 py-20 text-center md:py-24">
                <x-storefront.eyebrow class="hero-fade-1 mb-6">
                    {{ $content['hero_eyebrow'] ?? 'Your Opinion Matters' }}</x-storefront.eyebrow>
                <h1 class="font-display hero-fade-2 text-warm-100 mb-4 text-3xl leading-tight font-bold md:text-5xl">
                    {{ $survey->title }}
                </h1>
                @if ($survey->description)
                    <p class="font-script hero-fade-3 text-warm-400 text-2xl md:text-3xl">{{ $survey->description }}</p>
                @endif
            </div>
        </x-storefront.hero-section>

        {{-- Survey Form --}}
        <section class="bg-warm-50 px-4 py-16">
            <div class="mx-auto max-w-2xl" x-data="{ totalQuestions: {{ count($survey->questions) + 1 }} }">
                {{-- Progress Bar --}}
                <div class="mb-10">
                    <div class="mb-2 flex items-center justify-between">
                        <span class="text-warm-600 text-sm font-semibold">{{ count($survey->questions) }} questions</span>
                        <span class="text-warm-500 text-sm">Takes about {{ max(1, ceil(count($survey->questions) * 0.5)) }} min</span>
                    </div>
                    <div class="bg-warm-200 h-2 w-full rounded-full">
                        <div
                            class="from-warm-500 to-warm-400 h-full w-0 rounded-full bg-gradient-to-r"
                            id="surveyProgress"
                        ></div>
                    </div>
                </div>

                <form method="POST" action="{{ route('survey.submit', $survey) }}" class="space-y-6">
                    @csrf

                    {{-- Contact Info --}}
                    <div class="border-warm-200 rounded-2xl border bg-white p-6 md:p-8">
                        <p class="font-script text-warm-400 mb-4 text-2xl md:text-3xl">A little about you</p>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label class="text-warm-700 mb-1 block text-sm font-semibold">Your Name (optional)</label>
                                <input type="text" name="customer_name" class="input-field" />
                            </div>
                            <div>
                                <label class="text-warm-700 mb-1 block text-sm font-semibold">Your Email (optional)</label>
                                <input type="email" name="customer_email" class="input-field" />
                            </div>
                        </div>
                    </div>

                    {{-- Questions --}}
                    @foreach ($survey->questions as $index => $question)
                        <div class="border-warm-200 rounded-2xl border bg-white p-6 transition-all duration-300 hover:shadow-lg md:p-8">
                            <div class="mb-4 flex items-start gap-4">
                                <span class="bg-warm-500 text-warm-900 flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full text-sm font-bold">{{ $index + 1 }}</span>
                                <label class="font-display text-warm-900 text-lg leading-snug font-semibold">
                                    {{ $question['question'] }}
                                </label>
                            </div>

                            <div class="pl-12">
                                @if ($question['type'] === SurveyQuestionType::Rating->value)
                                    <div x-data="ratingPicker(0)" class="flex items-center gap-2">
                                        @for ($star = 1; $star <= 5; $star++)
                                            <button
                                                type="button"
                                                @click="set({{ $star }})"
                                                x-bind:class="isFilled({{ $star }}) ? 'text-warm-500' : 'text-warm-400 opacity-30'"
                                                class="cursor-pointer text-4xl transition-all duration-200 hover:scale-125"
                                            >
                                                ★
                                            </button>
                                        @endfor
                                        <input type="hidden" name="answers[{{ $index }}]" x-bind:value="rating" />
                                        <span
                                            class="text-warm-500 ml-3 text-sm font-semibold"
                                            x-show="rating > 0"
                                            x-text="['', 'Poor', 'Fair', 'Good', 'Great', 'Excellent'][rating]"
                                        ></span>
                                    </div>

                                @elseif ($question['type'] === SurveyQuestionType::Text->value)
                                    <textarea
                                        name="answers[{{ $index }}]"
                                        rows="3"
                                        class="input-field"
                                        placeholder="Type your answer..."
                                    ></textarea>

                                @elseif ($question['type'] === SurveyQuestionType::MultipleChoice->value)
                                    <div class="space-y-2">
                                        @foreach ($question['options'] ?? [] as $option)
                                            <label class="border-warm-200 hover:border-warm-400 flex cursor-pointer items-center gap-3 rounded-xl border p-3 transition-all duration-200 hover:shadow-sm">
                                                <input
                                                    type="radio"
                                                    name="answers[{{ $index }}]"
                                                    value="{{ $option }}"
                                                    class="accent-warm-500 h-4 w-4"
                                                />
                                                <span class="text-warm-800 font-medium">{{ $option }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach

                    {{-- Submit --}}
                    <div class="pt-4 text-center">
                        <x-storefront.button type="submit" size="xl">
                            {{ $content['submit_button'] ?? 'Submit Feedback' }}
                        </x-storefront.button>
                        <p class="text-warm-500 mt-4 text-sm">
                            {{ $content['submit_footer'] ?? 'Your feedback helps us bake better for you' }}
                        </p>
                    </div>
                </form>
            </div>
        </section>
    @endif
</x-layouts.storefront>
