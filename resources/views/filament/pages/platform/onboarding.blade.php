{{-- A focused setup page on the bare panel layout: no admin sidebar or topbar. Styles: css/filament/admin/_onboarding.css. --}}
<div class="kn-onboarding">
    <header class="kn-onboarding-bar">
        <div class="kn-onboarding-bakery">
            @if ($bakeryLogoUrl)
                <img src="{{ $bakeryLogoUrl }}" alt="" class="kn-onboarding-bakery-logo" />
            @else
                <span
                    class="kn-onboarding-bakery-initial"
                    aria-hidden="true"
                >{{ mb_strtoupper(mb_substr($bakeryName, 0, 1)) }}</span>
            @endif

            <span class="kn-onboarding-bakery-name">{{ $bakeryName }}</span>
        </div>

        <form method="post" action="{{ filament()->getLogoutUrl() }}">
            @csrf
            <button type="submit" class="kn-onboarding-later">Save and finish later</button>
        </form>
    </header>

    <main class="kn-onboarding-main">
        <aside
            class="kn-onboarding-steps"
            aria-labelledby="kn-onboarding-title"
            x-data="{ current: 0, labels: @js($stepLabels) }"
            x-on:onboarding-step-changed.window="current = $event.detail.index"
            wire:ignore
        >
            <h1 id="kn-onboarding-title" class="kn-onboarding-eyebrow">Set up your bakery</h1>

            <p class="kn-onboarding-steps-compact">
                Step <span x-text="current + 1">1</span> of {{ count($stepLabels) }} &middot;
                <span x-text="labels[current]">{{ $stepLabels[0] }}</span>
            </p>

            <ol class="kn-onboarding-steps-list">
                @foreach ($stepLabels as $index => $label)
                    <li
                        @class(['kn-onboarding-step', 'kn-onboarding-step-current' => $index === 0])
                        x-bind:class="{
                            'kn-onboarding-step-current': current === {{ $index }},
                            'kn-onboarding-step-done': current > {{ $index }},
                        }"
                        @if ($index === 0) aria-current="step" @endif
                        x-bind:aria-current="current === {{ $index }} ? 'step' : null"
                    >
                        <span class="kn-onboarding-step-marker" aria-hidden="true">
                            <span x-show="current <= {{ $index }}">{{ $index + 1 }}</span>
                            <x-filament::icon
                                :icon="\Filament\Support\Icons\Heroicon::Check"
                                x-show="current > {{ $index }}"
                                x-cloak
                            />
                        </span>
                        <span>{{ $label }}</span>
                        <span class="fi-sr-only" x-text="current > {{ $index }} ? '(done)' : ''"></span>
                    </li>
                @endforeach
            </ol>
        </aside>

        <div>{{ $this->content }}</div>
    </main>

    <x-filament-actions::modals />
</div>
