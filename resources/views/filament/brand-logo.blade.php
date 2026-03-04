@php
    use App\Models\Setting;
    $storeName = Setting::get('store_name', 'KneadIt');
@endphp

<div class="flex items-center space-x-2">
    <div class="flex items-center justify-center w-8 h-8 bg-gradient-to-br from-amber-500 to-orange-600 rounded-lg">
        <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24">
            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
        </svg>
    </div>
    <span class="font-bold text-lg text-gray-800 dark:text-gray-200">
        {{ $storeName }}
    </span>
</div>