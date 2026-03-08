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
            
            @php
                $footerSocial = json_decode(\App\Models\Setting::get('social_media_links', '{}'), true);
            @endphp

            @if(!empty(array_filter($footerSocial ?? [])))
            <div class="flex justify-center gap-4 mb-6">
                @if(!empty($footerSocial['facebook']))
                <a href="{{ $footerSocial['facebook'] }}" target="_blank" rel="noopener" class="w-10 h-10 rounded-full flex items-center justify-center transition-colors duration-200" style="background: rgba(255,255,255,0.1); color: var(--warm-400);">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                </a>
                @endif
                @if(!empty($footerSocial['instagram']))
                <a href="{{ $footerSocial['instagram'] }}" target="_blank" rel="noopener" class="w-10 h-10 rounded-full flex items-center justify-center transition-colors duration-200" style="background: rgba(255,255,255,0.1); color: var(--warm-400);">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg>
                </a>
                @endif
                @if(!empty($footerSocial['twitter']))
                <a href="{{ $footerSocial['twitter'] }}" target="_blank" rel="noopener" class="w-10 h-10 rounded-full flex items-center justify-center transition-colors duration-200" style="background: rgba(255,255,255,0.1); color: var(--warm-400);">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/></svg>
                </a>
                @endif
            </div>
            @endif

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