<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? \App\Models\Setting::get('store_name', 'Artisan Bakery') }}</title>
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
        <div class="bg-warm-800 bg-opacity-90 backdrop-blur-sm rounded-full px-6 py-3 border border-warm-600 border-opacity-20" style="background: var(--warm-800);">
            <div class="nav-desktop items-center space-x-2">
                <a href="{{ url('/') }}" class="nav-link font-script text-lg" style="color: var(--warm-400); font-size: 1.15rem;">
                    {{ \App\Models\Setting::get('store_name', 'Our Bakery') }}
                </a>
                <span style="color: var(--warm-600); opacity: 0.4;">|</span>
                <a href="{{ url('/') }}" class="nav-link font-display {{ request()->is('/') ? 'active' : '' }}">Home</a>
                <a href="{{ route('storefront.menu') }}" class="nav-link font-display {{ request()->routeIs('storefront.menu') ? 'active' : '' }}">Menu</a>
                <a href="{{ route('order.create') }}" class="nav-link font-display {{ request()->routeIs('order.create') ? 'active' : '' }}">Order</a>
                <a href="{{ route('contact.show') }}" class="nav-link font-display {{ request()->routeIs('contact.show') ? 'active' : '' }}">Contact</a>
            </div>
            
            <!-- Mobile menu button -->
            <div class="nav-mobile" x-data="{ open: false }">
                <div class="flex items-center justify-between">
                    <a href="{{ url('/') }}" class="font-script text-lg" style="color: var(--warm-400); text-decoration: none;">
                        {{ \App\Models\Setting::get('store_name', 'Our Bakery') }}
                    </a>
                    <button @click="open = !open" class="nav-link font-display ml-4">
                        <span x-text="open ? '✕' : '☰'"></span>
                    </button>
                </div>
                <div x-show="open" x-collapse class="mt-4 space-y-2">
                    <a href="{{ url('/') }}" class="block nav-link font-display {{ request()->is('/') ? 'active' : '' }}">Home</a>
                    <a href="{{ route('storefront.menu') }}" class="block nav-link font-display {{ request()->routeIs('storefront.menu') ? 'active' : '' }}">Menu</a>
                    <a href="{{ route('order.create') }}" class="block nav-link font-display {{ request()->routeIs('order.create') ? 'active' : '' }}">Order</a>
                    <a href="{{ route('contact.show') }}" class="block nav-link font-display {{ request()->routeIs('contact.show') ? 'active' : '' }}">Contact</a>
                </div>
            </div>
        </div>
    </nav>

    <main class="min-h-screen pt-24">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer style="background: var(--warm-900); color: var(--warm-200);" class="py-16">
        <div class="max-w-6xl mx-auto px-4 text-center">
            <div class="h-1 mb-12" style="background: linear-gradient(to right, transparent, var(--warm-500), transparent);"></div>
            
            <h3 class="font-display text-2xl mb-2">{{ \App\Models\Setting::get('store_name', 'Our Bakery') }}</h3>
            <p class="font-script text-xl mb-6" style="color: var(--warm-400);">Baked with love, served with care</p>
            
            @php
                $footerAddress = \App\Models\Setting::get('store_address');
                $footerPhone = \App\Models\Setting::get('store_phone');
                $footerEmail = \App\Models\Setting::get('store_email');
            @endphp
            
            @if($footerAddress || $footerPhone || $footerEmail)
            <div class="flex flex-wrap justify-center gap-6 mb-8 text-sm" style="color: var(--warm-400);">
                @if($footerAddress)
                <span>{{ $footerAddress }}</span>
                @endif
                @if($footerPhone)
                <span>{{ $footerPhone }}</span>
                @endif
                @if($footerEmail)
                <span>{{ $footerEmail }}</span>
                @endif
            </div>
            @endif
            
            <div class="text-sm leading-relaxed space-y-3" style="color: var(--warm-400);">
                <p>&copy; {{ date('Y') }} {{ \App\Models\Setting::get('store_name', 'Our Bakery') }}. All rights reserved.</p>
                <p class="text-xs opacity-60">Powered by KneadIt</p>
            </div>
        </div>
    </footer>

    @yield('scripts')
</body>
</html>