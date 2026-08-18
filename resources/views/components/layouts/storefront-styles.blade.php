    <style @cspnonce>
        /* ===== Base / Classic Theme (default) ===== */
        :root {
            --warm-900: #1c1410;
            --warm-800: #2a1f18;
            --warm-700: #4a3728;
            --warm-600: #8b6844;
            --warm-500: #d4920c;
            --warm-400: #e8b04a;
            --warm-300: #f5d88e;
            --warm-200: #faf4e8;
            --warm-100: #fef9ef;
            --warm-50: #fffdf7;
            --font-display: 'Playfair Display', serif;
            --font-body: 'Inter', sans-serif;
            --font-script: 'Dancing Script', cursive;
            --radius-card: 12px;
            --radius-btn: 8px;
        }

        /* ===== Modern Theme ===== */
        [data-theme="modern"] {
            --warm-900: #111827;
            --warm-800: #1f2937;
            --warm-700: #374151;
            --warm-600: #d4920c;
            --warm-500: #f59e0b;
            --warm-400: #fbbf24;
            --warm-300: #e5e7eb;
            --warm-200: #f3f4f6;
            --warm-100: #ffffff;
            --warm-50: #ffffff;
            --font-display: 'Inter', sans-serif;
            --font-body: 'Inter', sans-serif;
            --font-script: 'Inter', sans-serif;
            --radius-card: 2px;
            --radius-btn: 2px;
        }

        /* ===== Rustic Theme ===== */
        [data-theme="rustic"] {
            --warm-900: #3d3527;
            --warm-800: #4a4035;
            --warm-700: #5c5245;
            --warm-600: #6b7c5e;
            --warm-500: #8a9e76;
            --warm-400: #a3b48f;
            --warm-300: #d5ccba;
            --warm-200: #e8e0d0;
            --warm-100: #f5f0e8;
            --warm-50: #faf7f2;
            --font-display: 'Caveat', cursive;
            --font-body: 'Inter', sans-serif;
            --font-script: 'Caveat', cursive;
            --radius-card: 16px;
            --radius-btn: 16px;
        }

        /* ===== Elegant Theme ===== */
        [data-theme="elegant"] {
            --warm-900: #1a1a1a;
            --warm-800: #2d2d2d;
            --warm-700: #444444;
            --warm-600: #b8960c;
            --warm-500: #c9a827;
            --warm-400: #d4b84a;
            --warm-300: #e0e0e0;
            --warm-200: #f5f5f5;
            --warm-100: #ffffff;
            --warm-50: #ffffff;
            --font-display: 'Cormorant Garamond', serif;
            --font-body: 'Inter', sans-serif;
            --font-script: 'Cormorant Garamond', serif;
            --radius-card: 0px;
            --radius-btn: 0px;
        }

        body {
            font-family: var(--font-body);
            color: var(--warm-900);
            background: var(--warm-100);
        }

        .font-display {
            font-family: var(--font-display);
        }

        .font-script {
            font-family: var(--font-script);
        }

        .btn-primary {
            background: var(--warm-600);
            color: white;
            padding: 12px 24px;
            border-radius: var(--radius-btn);
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }

        .btn-primary:hover {
            background: var(--warm-700);
            transform: translateY(-1px);
        }

        .btn-primary:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        .spinner {
            display: inline-block;
            width: 1.25rem;
            height: 1.25rem;
            border: 2px solid currentColor;
            border-right-color: transparent;
            border-radius: 50%;
            animation: spin 0.6s linear infinite;
        }

        .nav-link {
            color: var(--warm-200);
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 100px;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .nav-link:hover {
            background: color-mix(in srgb, var(--warm-500) 20%, transparent);
            color: var(--warm-400);
        }

        .nav-link.active {
            background: var(--warm-500);
            color: var(--warm-900);
        }

        .nav-dropdown-link {
            display: block;
            color: var(--warm-200);
            text-decoration: none;
            padding: 10px 20px;
            font-weight: 500;
            font-size: 0.9rem;
            transition: all 0.2s ease;
        }

        .nav-dropdown-link:hover {
            background: color-mix(in srgb, var(--warm-500) 15%, transparent);
            color: var(--warm-400);
        }

        .nav-dropdown-link.active {
            color: var(--warm-400);
            background: color-mix(in srgb, var(--warm-500) 10%, transparent);
        }

        .card {
            background: white;
            border-radius: var(--radius-card);
            box-shadow: 0 4px 20px rgba(28, 20, 16, 0.1);
            border: 1px solid var(--warm-200);
        }

        .input-field {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid var(--warm-200);
            border-radius: var(--radius-btn);
            font-size: 16px;
            transition: border-color 0.3s ease;
            background: white;
        }

        .input-field:focus {
            outline: none;
            border-color: var(--warm-500);
        }

        .storefront-input {
            width: 100%;
            padding: 0.875rem 1.25rem;
            border-radius: 0.75rem;
            border: 1.5px solid var(--warm-200);
            background: white;
            font-family: var(--font-body);
            font-size: 1rem;
            color: var(--warm-900);
            transition: border-color 0.3s, box-shadow 0.3s;
            outline: none;
        }
        .storefront-input:focus {
            border-color: var(--warm-500);
            box-shadow: 0 0 0 3px color-mix(in srgb, var(--warm-500) 10%, transparent);
        }
        .storefront-input::placeholder {
            color: var(--warm-400);
        }

        .storefront-input-dark {
            width: 100%;
            padding: 0.875rem 1.25rem;
            border-radius: 0.75rem;
            border: 1.5px solid color-mix(in srgb, var(--warm-600) 25%, transparent);
            background: var(--warm-800);
            font-family: var(--font-body);
            font-size: 1rem;
            color: var(--warm-200);
            transition: border-color 0.3s, box-shadow 0.3s;
            outline: none;
        }
        .storefront-input-dark:focus {
            border-color: var(--warm-500);
            box-shadow: 0 0 0 3px color-mix(in srgb, var(--warm-500) 15%, transparent);
        }
        .storefront-input-dark::placeholder {
            color: var(--warm-600);
        }

        .text-primary {
            color: var(--warm-600);
        }

        .bg-primary {
            background: var(--warm-600);
        }

        .border-primary {
            border-color: var(--warm-500);
        }

        /* Better base typography scale */
        h1 { line-height: 1.05; }
        h2 { line-height: 1.15; }
        h3 { line-height: 1.25; }

        /* Elegant theme: extra letter-spacing and thin borders */
        [data-theme="elegant"] .font-display {
            letter-spacing: 0.05em;
            font-weight: 300;
        }
        [data-theme="elegant"] .card {
            border-width: 1px;
            box-shadow: none;
        }

        /* Rustic theme: larger heading sizes for handwritten feel */
        [data-theme="rustic"] .font-display {
            font-weight: 600;
        }

        /* Modern theme: stronger shadows */
        [data-theme="modern"] .card {
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.12);
            border: none;
        }

        /* ===== Accessibility: Focus indicators ===== */
        .btn-primary:focus-visible {
            outline: 2px solid var(--warm-500);
            outline-offset: 2px;
        }

        .nav-link:focus-visible,
        .nav-dropdown-link:focus-visible {
            outline: 2px solid var(--warm-400);
            outline-offset: 2px;
        }

        .input-field:focus-visible {
            outline: 2px solid var(--warm-500);
            outline-offset: -2px;
        }

        a:focus-visible {
            outline: 2px solid var(--warm-500);
            outline-offset: 2px;
            border-radius: 2px;
        }

        button:focus-visible {
            outline: 2px solid var(--warm-500);
            outline-offset: 2px;
        }

        /* ===== Accessibility: Reduced motion ===== */
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
                scroll-behavior: auto !important;
            }
        }

        @media (max-width: 768px) {
            .nav-mobile {
                display: block;
            }

            .nav-desktop {
                display: none;
            }
        }

        @media (min-width: 769px) {
            .nav-mobile {
                display: none;
            }

            .nav-desktop {
                display: flex;
            }
        }
    </style>
