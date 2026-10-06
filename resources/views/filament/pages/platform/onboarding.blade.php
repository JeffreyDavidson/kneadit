<x-filament-panels::page>
    <style @cspnonce>
        .fi-page {
            max-width: 800px;
            margin: 0 auto;
        }
        .onboarding-intro {
            margin-bottom: var(--kn-space-6);
            text-align: center;
        }
        .onboarding-intro-heading {
            color: var(--kn-ink);
            font-family: var(--kn-font-display);
            font-size: 1.75rem;
            font-weight: 400;
            line-height: 2.125rem;
        }
        .onboarding-intro-copy {
            color: var(--kn-ink-2);
            margin-top: var(--kn-space-1);
        }
    </style>

    <div class="onboarding-intro">
        <h2 class="onboarding-intro-heading">Let's set up your bakery</h2>
        <p class="onboarding-intro-copy">Just a few steps and you'll be ready to go</p>
    </div>

    {{ $this->content }}
</x-filament-panels::page>
