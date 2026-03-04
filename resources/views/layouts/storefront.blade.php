<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'KneadIt - Artisan Bakery SaaS' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Dancing+Script:wght@400;600;700&family=Inter:wght@300;400;500;600;700&family=Playfair+Display:ital,wght@0,400;0,500;0,600;0,700;1,400;1,500&display=swap" rel="stylesheet">
    
    <style>
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
        }

        body {
            font-family: 'Inter', sans-serif;
            color: var(--warm-900);
            background: var(--warm-100);
        }

        .font-display {
            font-family: 'Playfair Display', serif;
        }

        .font-script {
            font-family: 'Dancing Script', cursive;
        }

        .btn-primary {
            background: var(--warm-600);
            color: white;
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }

        .btn-primary:hover {
            background: var(--warm-700);
            transform: translateY(-1px);
        }

        .btn-secondary {
            background: var(--warm-200);
            color: var(--warm-900);
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
            border: 1px solid var(--warm-300);
            cursor: pointer;
        }

        .btn-secondary:hover {
            background: var(--warm-300);
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
            background: rgba(212, 146, 12, 0.2);
            color: var(--warm-400);
        }

        .nav-link.active {
            background: var(--warm-500);
            color: var(--warm-900);
        }

        .card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(28, 20, 16, 0.1);
            border: 1px solid var(--warm-200);
        }

        .input-field {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid var(--warm-200);
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s ease;
            background: white;
        }

        .input-field:focus {
            outline: none;
            border-color: var(--warm-500);
        }

        .text-primary {
            color: var(--warm-600);
        }

        .text-secondary {
            color: var(--warm-700);
        }

        .bg-primary {
            background: var(--warm-600);
        }

        .bg-secondary {
            background: var(--warm-200);
        }

        .border-primary {
            border-color: var(--warm-500);
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
    
    @yield('styles')
</head>
<body @yield('body_attrs')>

    <!-- Navigation -->
    <nav class="fixed top-4 left-1/2 transform -translate-x-1/2 z-50">
        <div class="bg-warm-800 bg-opacity-90 backdrop-blur-sm rounded-full px-6 py-3 border border-warm-600 border-opacity-20">
            <div class="nav-desktop items-center space-x-2">
                <a href="{{ route('home') }}" class="nav-link font-display {{ request()->routeIs('home') ? 'active' : '' }}">Home</a>
                <a href="{{ route('order') }}" class="nav-link font-display {{ request()->routeIs('order') ? 'active' : '' }}">Order</a>
                <a href="{{ route('contact') }}" class="nav-link font-display {{ request()->routeIs('contact') ? 'active' : '' }}">Contact</a>
            </div>
            
            <!-- Mobile menu button -->
            <div class="nav-mobile" x-data="{ open: false }">
                <button @click="open = !open" class="nav-link font-display">
                    <span x-text="open ? '✕' : '☰'"></span>
                </button>
                <div x-show="open" x-collapse class="mt-4 space-y-2">
                    <a href="{{ route('home') }}" class="block nav-link font-display {{ request()->routeIs('home') ? 'active' : '' }}">Home</a>
                    <a href="{{ route('order') }}" class="block nav-link font-display {{ request()->routeIs('order') ? 'active' : '' }}">Order</a>
                    <a href="{{ route('contact') }}" class="block nav-link font-display {{ request()->routeIs('contact') ? 'active' : '' }}">Contact</a>
                </div>
            </div>
        </div>
    </nav>

    <main class="min-h-screen pt-24">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-warm-900 text-warm-200 py-16">
        <div class="max-w-6xl mx-auto px-4 text-center">
            <div class="h-1 bg-gradient-to-r from-transparent via-warm-500 to-transparent mb-12"></div>
            
            <h3 class="font-display text-2xl mb-2">KneadIt</h3>
            <p class="font-script text-xl text-warm-400 mb-6">Artisan Bakery Management</p>
            
            <div class="inline-block border border-warm-600 border-opacity-30 rounded-full px-6 py-2 mb-6">
                <span class="text-sm text-warm-400 tracking-wide">Powered by Innovation</span>
            </div>
            
            <div class="text-sm text-warm-400 leading-relaxed space-y-1">
                <p>Built for artisan bakeries who demand excellence</p>
                <p>© {{ date('Y') }} KneadIt. All rights reserved.</p>
            </div>
        </div>
    </footer>

    @yield('scripts')
</body>
</html>