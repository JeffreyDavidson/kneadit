<x-filament-panels::page>
    <style>
        .fi-page {
            max-width: 800px;
            margin: 0 auto;
        }
        .fi-wizard-header {
            background: linear-gradient(135deg, #fdf8f2 0%, #f5e6d0 100%);
            border-bottom: 2px solid #e8d0b0;
            padding: 1.5rem;
            border-radius: 12px 12px 0 0;
        }
        .fi-wizard {
            border: 1px solid #e8d0b0;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(61, 35, 20, 0.08), 0 2px 4px -2px rgba(61, 35, 20, 0.04);
        }
        .fi-wizard-step-indicator-active {
            background-color: #6b4c3b !important;
            color: white !important;
        }
    </style>

    <div class="text-center mb-6">
        <h2 class="text-2xl font-bold" style="color: #3d2314;">Let's set up your bakery</h2>
        <p class="mt-1" style="color: #6b4c3b;">Just a few steps and you'll be ready to go</p>
    </div>

    {{ $this->content }}
</x-filament-panels::page>
